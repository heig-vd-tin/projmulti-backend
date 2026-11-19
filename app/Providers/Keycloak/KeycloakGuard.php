<?php

namespace App\Providers\Keycloak;

use App\Constants\UserRole;
use App\Models\Orientation;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Authentification par jeton Keycloak (Bearer). À la première connexion, l'utilisateur est
 * créé à partir de l'annuaire LDAP de l'école (rôle et orientation).
 *
 * source: https://github.com/robsontenorio/laravel-keycloak-guard
 * adapté par Alec Berney, Tristan Lieberherr et Tony Maulaz
 */
class KeycloakGuard implements Guard
{
    /**
     * Orientation déduite de l'attribut LDAP « description » de l'étudiant.
     * Règles testées dans l'ordre : tous les mots-clés d'une règle doivent être présents.
     */
    private const ORIENTATION_RULES = [
        ['acronym' => 'SIC', 'keywords' => ['conception', 'machine']],
        ['acronym' => 'EAI', 'keywords' => ['automatisation']],
        ['acronym' => 'EEM', 'keywords' => ['lectronique']],
        ['acronym' => 'EN', 'keywords' => ['énergétiques']],
        ['acronym' => 'THI', 'keywords' => ['thermique']],
        ['acronym' => 'MI', 'keywords' => ['microtechnique']],
    ];

    private ?Authenticatable $user = null;
    private ?object $decodedToken = null;

    public function __construct(private UserProvider $provider, private Request $request)
    {
        // Développement local uniquement : se connecter sans Keycloak en choisissant l'utilisateur.
        if (app()->isLocal() && config('app.debug')) {
            $devUserId = $request->header('X-Dev-User') ?: config('keycloak.dev_user_id');
            if ($devUserId) {
                $user = $this->provider->retrieveById($devUserId);
                if ($user) {
                    $this->setUser($user);
                }
                return;
            }
        }
        $this->authenticate();
    }

    private function authenticate(): void
    {
        $token = $this->request->bearerToken();
        if (!$token) {
            return;
        }
        try {
            $this->decodedToken = Token::decode(config('keycloak.realm_public_key'), $token);
        } catch (\Throwable $e) {
            return; // jeton invalide ou expiré : utilisateur non authentifié (401)
        }

        $user = $this->provider->retrieveByCredentials([
            config('keycloak.user_provider_credential') => $this->decodedToken->{config('keycloak.token_principal_attribute')},
        ]);

        if (is_null($user)) {
            $user = $this->createUserFromLdap();
        }
        if ($user) {
            $this->setUser($user);
        }
    }

    private function createUserFromLdap(): ?User
    {
        $email = $this->decodedToken->email ?? null;
        $ldapData = $email ? $this->ldapRequest($email) : null;
        if (!$ldapData) {
            Log::error('LDAP : utilisateur introuvable ou annuaire injoignable', ['email' => $email]);
            return null;
        }

        $existing = User::where('email', $email)->first();
        if ($existing) {
            return $existing;
        }

        $user = new User([
            'firstname' => $this->decodedToken->given_name,
            'lastname' => $this->decodedToken->family_name,
            'email' => $email,
            'role' => $this->mapRole($ldapData),
        ]);
        if ($user->isTeacher()) {
            $user->initials = 'N/A';
        } elseif ($user->isStudent()) {
            $acronym = $this->mapOrientation($ldapData);
            $user->orientation_id = $acronym ? Orientation::where('acronym', $acronym)->value('id') : null;
        }
        $user->save();
        Log::info('Nouvel utilisateur créé', ['email' => $email, 'role' => $user->role]);
        return $user;
    }

    public function mapRole(array $ldapData): string
    {
        $dn = $ldapData['dn'] ?? '';
        if (str_contains($dn, 'OU=Enseignants') || str_contains($dn, 'OU=Assistants')) {
            return UserRole::PROFESSOR;
        }
        return UserRole::STUDENT;
    }

    public function mapOrientation(array $ldapData): ?string
    {
        $description = $ldapData['description'][0] ?? '';
        if (!mb_check_encoding($description, 'UTF-8')) {
            $description = mb_convert_encoding($description, 'UTF-8', 'ISO-8859-1');
        }
        $description = mb_strtolower($description);

        foreach (self::ORIENTATION_RULES as $rule) {
            $matches = array_filter($rule['keywords'], fn ($k) => str_contains($description, mb_strtolower($k)));
            if (count($matches) === count($rule['keywords'])) {
                return $rule['acronym'];
            }
        }
        Log::warning('Orientation non reconnue', [
            'description' => $description,
            'name' => $this->decodedToken->family_name ?? null,
        ]);
        return null;
    }

    private function ldapRequest(string $email): ?array
    {
        $conn = ldap_connect(config('services.ldap.server'));
        if (!$conn) {
            return null;
        }
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

        $bound = false;
        for ($i = 0; $i < 5 && !$bound; $i++) {
            $bound = @ldap_bind($conn, config('services.ldap.user'), config('services.ldap.password'));
            if (!$bound) {
                Log::warning('LDAP bind échoué', ['tentative' => $i + 1, 'erreur' => ldap_error($conn)]);
            }
        }
        if (!$bound) {
            ldap_close($conn);
            return null;
        }

        $filter = '(mail=' . ldap_escape($email, '', LDAP_ESCAPE_FILTER) . '*)';
        $result = @ldap_search($conn, config('services.ldap.base_dn'), $filter, ['cn', 'sn', 'department', 'title', 'description']);
        $entries = $result ? ldap_get_entries($conn, $result) : null;
        ldap_close($conn);

        return ($entries && $entries['count'] > 0) ? $entries[0] : null;
    }

    public function check(): bool
    {
        return !is_null($this->user);
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function user(): ?Authenticatable
    {
        return $this->user;
    }

    public function id(): int|string|null
    {
        return $this->user?->getAuthIdentifier();
    }

    public function validate(array $credentials = []): bool
    {
        return !is_null($this->provider->retrieveByCredentials($credentials));
    }

    public function hasUser(): bool
    {
        return !is_null($this->user);
    }

    public function setUser(Authenticatable $user): static
    {
        $this->user = $user;
        return $this;
    }

    /** Jeton décodé de l'utilisateur connecté. */
    public function token(): ?string
    {
        return $this->decodedToken ? json_encode($this->decodedToken) : null;
    }
}

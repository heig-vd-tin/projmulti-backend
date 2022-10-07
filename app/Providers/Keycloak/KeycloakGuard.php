<?php

namespace App\Providers\Keycloak;

use App\Constants\UserRole;
use App\Models\Orientation;
use App\Models\User;
use App\Providers\Keycloak\Exceptions\TokenException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Auth;
use Session;

/**
 * source: https://github.com/robsontenorio/laravel-keycloak-guard
 * author: Robson Tenório https://github.com/robsontenorio
 * author: adapted by Alec Berney https://github.com/alecberney
 * author: adapted once more by Tristan Lieberherr
 */
class KeycloakGuard implements Guard
{
    private $config;
    private $user;
    private $provider;
    private $decodedToken;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->config = config('keycloak');
        $this->user = null;
        $this->provider = $provider;
        $this->decodedToken = null;
        $this->request = $request;

        if (false) {
            $this->ldap_request();
        }

        // tmz : disable keycloak for local development
        $id = env('ID_USER', null);
        if (env('APP_DEBUG', false) && $id) {
            $user = $this->provider->retrieveById($id);
            $this->setUser($user);
        } else {
            $this->authenticate();
            // dd('user', $user);
        }
    }

    function ldap_request()
    {
        $ldapserver = 'ldap.heig-vd.ch';
        $ldapuser = 'iai-ldap';
        $ldappass = env('LDAP_PASSWORD', null);
        $ldaptree = "DC=einet,DC=ad,DC=eivd,DC=ch";
        $ldapconn = ldap_connect($ldapserver) or die("Could not connect to LDAP server.");

        if ($ldapconn) {

            $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass) or die("Error trying to bind: " . ldap_error($ldapconn));

            if ($ldapbind) {
                echo "LDAP bind successful...<br /><br />";

                $person = "maulaz";
                $filtre = "(|(sn=$person*)(cn=$person*))"; //"(cn=*)"
                $restriction = array("cn", "sn", "mail", "company", "department", "title", "userPrincipalName", "mailNickname");

                $result = ldap_search($ldapconn, $ldaptree, $filtre, $restriction) or die("Error in search query: " . ldap_error($ldapconn));
                $data = ldap_get_entries($ldapconn, $result);
                dd("connexion", $result, $data);
            }

            ldap_close($ldapconn);
        }
        dd("not connected");
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check(): bool
    {
        return !is_null($this->user());
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (is_null($this->user)) {
            return null;
        }

        return $this->user;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id()
    {
        if ($user = $this->user()) {
            return $this->user()->id;
        }
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {

        if (!$this->decodedToken) {
            return false;
        }

        // Load user from BD
        $user = $this->provider->retrieveByCredentials($credentials);

        if (!$user) {
            // Create user in BD if not exists
            $user = new User([
                'firstname' => $this->decodedToken->given_name,
                'lastname' => $this->decodedToken->family_name,
                'email' => $this->decodedToken->email,
                // 'role' => $this->decodedToken->role,
                'role' => UserRole::STUDENT,
            ]);
            // if (!in_array($this->decodedToken->role, UserRole::TEACHERS)) {
            //     $user->orientation_id = Orientation::where('acronym', $this->decodedToken->orientation)->firstOrFail()->id;
            // }
            $user->orientation_id = Orientation::find(1)->id;

            $user->save();
        }

        $this->setUser($user);

        return true;
    }

    /**
     * Determine if the guard has a user instance.
     *
     * @return bool
     */
    public function hasUser(): bool
    {
        return !is_null($this->user());
    }

    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
    }

    /**
     * Returns full decoded JWT token from athenticated user
     *
     * @return mixed|null
     */
    public function token()
    {
        return json_encode($this->decodedToken);
    }

    /**
     * Decode token, validate and authenticate user
     *
     * @return mixed
     */
    private function authenticate()
    {
        try {
            $this->decodedToken = Token::decode($this->config['realm_public_key'], $this->request->bearerToken());
        } catch (\Exception $e) {
            return false;
            // throw new TokenException($e->getMessage());
        }

        if ($this->decodedToken) {
            $this->validate([
                $this->config['user_provider_credential'] => $this->decodedToken->{$this->config['token_principal_attribute']}
            ]);
        }
    }
}

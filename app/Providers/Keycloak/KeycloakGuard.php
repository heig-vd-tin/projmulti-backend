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
    private $ldapData;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->config = config('keycloak');
        $this->user = null;
        $this->provider = $provider;
        $this->decodedToken = null;
        $this->request = $request;
       
        // tmz : disable keycloak for local development
        $id = env('ID_USER', null);
        if (env('APP_DEBUG', false) && $id) {
            $user = $this->provider->retrieveById($id);
            $this->setUser($user);
        } else {
            $this->authenticate();
	   
	    //dd("aaaa", Auth::id());
            // dd("user", $this->user);
        }
    }

    public function map_roles($ldap_data): string
    {
        $dn = $ldap_data['dn'];
        if (strpos($dn, 'OU=Enseignants') !== false) return UserRole::PROFESSOR;
        if (strpos($dn, 'OU=Assistants') !== false) return UserRole::PROFESSOR;
        if (strpos($dn, 'OU=Etudiants') !== false) return UserRole::STUDENT;
        else return UserRole::STUDENT;
    }

    public function map_orientation($ldap_data): string
    {
        $department = json_decode(json_encode($ldap_data['department'][0], JSON_INVALID_UTF8_IGNORE));
        switch ($department) {
            case "Technique et IT - Electronique et Automatisation industrielle":
                return "EAI";
            case "Technique et IT - Electronique embarque et Mcatronique":
                return "EEM";
            case "Technique et IT - Systmes nergtiques":
                return "EN";
            case "Technique et IT - Energtique du btiment":
                return "EBA";
            case "Technique et IT - Thermique industrielle":
                return "THI";
            case "Technique et IT - Thermotronique":
                return "THO";
            case "Technique et IT - Ingnierie et gestion industrielles":
                return "IGIS";
            case "Technique et IT - Logistique et organisation industrielles":
                return "IGLO";
            case "Technique et IT - Qualit et performances industrielles":
                return "IGQP";
            case "Technique et IT - Microtechniques":
                return "MI";
            case "Technique et IT - Conception":
                return "SIC";
            case "Technique et IT - Informatique embarque":
                return "IE";
            case "Technique et IT - Logiciel":
                return "IL";
            case "Technique et IT - Systmes de gestion":
                return "SG";
            case "Technique et IT - Ingnierie des donnes":
                return "ISCD";
            case "Technique et IT - Systmes informatiques embarqus":
                return "ISCE";
            case "Technique et IT - Informatique logicielle":
                return "ISCL";
            case "Technique et IT - Rseaux et systmes":
                return "ISCR";
            case "Technique et IT - Scurit informatique":
                return "ISCS";
            case "Technique et IT - Rseaux et services":
                return "TR";
            case "Technique et IT - Scurit de l'information":
                return "TS";
            case "Economie et services - Economie d'entreprise":
                return "EE";
            case "Technique et IT - Ingnierie des mdias":
                return "IM";
            case "Architecture, construction et planification":
                return "!!!";
            default:
                return "N/A";
        }
        /*
        TIN
            ELCI
                EAI: "Technique et IT - Electronique et Automatisation industrielle"
                EEM: "Technique et IT - Electronique embarque et Mcatronique"
                EN: "Technique et IT - Systmes nergtiques"
            ETE
                EBA: "Technique et IT - Energtique du btiment"
                THI: "Technique et IT - Thermique industrielle"
                THO: "Technique et IT - Thermotronique"
            IGIS
                IGIS: "Technique et IT - Ingnierie et gestion industrielles"
                IGLO: "Technique et IT - Logistique et organisation industrielles"
                IGQP: "Technique et IT - Qualit et performances industrielles"
            MTEC
                MI: "Technique et IT - Microtechniques"
            SYND
                SIC: "Technique et IT - Conception"
        TIC
            INFO
                IE: "Technique et IT - Informatique embarque"
                IL: "Technique et IT - Logiciel"
                SG: "Technique et IT - Systmes de gestion"
            ISC
                ISCD: "Technique et IT - Ingnierie des donnes"
                ISCE: "Technique et IT - Systmes informatiques embarqus"
                ISCL: "Technique et IT - Informatique logicielle"
                ISCR: "Technique et IT - Rseaux et systmes"
                ISCS: "Technique et IT - Scurit informatique"
            TELE
                TR: "Technique et IT - Rseaux et services"
                TS: "Technique et IT - Scurit de l'information"
        HEG
            EE
                EE: "Economie et services - Economie d'entreprise"
        COMEM+
            GCOM
                IM: "Technique et IT - Ingnierie des mdias"
            IGES
        ECG
            GEC
                GCI: "Architecture, construction et planification"
                GEN: "Architecture, construction et planification"
                GGT: "Architecture, construction et planification"
        */
    }

    public function ldap_request($user_email)
    {
        $ldapserver = 'ldap.heig-vd.ch';
        $ldapuser = 'iai-ldap';
        $ldappass = env('LDAP_PASSWORD', null);
        $ldaptree = "DC=einet,DC=ad,DC=eivd,DC=ch";
        $ldapconn = ldap_connect($ldapserver) or die("Could not connect to LDAP server.");
        if ($ldapconn) {
            $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass) or die("Error trying to bind: " . ldap_error($ldapconn));
            if ($ldapbind) {
                $filtre = "(|(mail=$user_email*))";
                $restriction = array("cn", "sn", "department", "title");
                $result = ldap_search($ldapconn, $ldaptree, $filtre, $restriction) or die("Error in search query: " . ldap_error($ldapconn));
                $this->ldapData = ldap_get_entries($ldapconn, $result)[0];
            }
            ldap_close($ldapconn);
        }
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
        if (!$this->decodedToken) return false;
//	dd("cred : ",$credentials);
        $user = $this->provider->retrieveByCredentials($credentials);
	//dd("aa user", $user);
	//dd("aaaa",$user, $this->decodedToken);
        if (!$user) {
            $this->ldap_request($this->decodedToken->email);
            if (!$this->ldapData) return false;
            $user = new User([
                'firstname' => $this->decodedToken->given_name,
                'lastname' => $this->decodedToken->family_name,
                'email' => $this->decodedToken->email,
                'role' => $this->map_roles($this->ldapData)
            ]);
            if ($user->isTeacher()) {
                $user->initials = "N/A";
            } elseif ($user->isStudent()) {
                $user->orientation_id = Orientation::where('acronym', $this->map_orientation($this->ldapData))->firstOrFail()->id;
            }
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
//	    dd("kjs",$this->request->header(), $this, $this->request->bearerToken());
        try {
            $this->decodedToken = Token::decode($this->config['realm_public_key'], $this->request->bearerToken());
        } catch (\Exception $e) {
            return false;
            // throw new TokenException($e->getMessage());
        }

        if ($this->decodedToken) {
		//dd("decode", $this->decodedToken );
            $this->validate([
                $this->config['user_provider_credential'] => $this->decodedToken->{$this->config['token_principal_attribute']}
            ]);
        }
    }
}

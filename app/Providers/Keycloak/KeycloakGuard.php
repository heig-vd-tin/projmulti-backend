<?php

namespace App\Providers\Keycloak;

use Illuminate\Support\Facades\Log;
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
        $description = json_decode(json_encode($ldap_data['description'][0], JSON_INVALID_UTF8_IGNORE));
        switch (strtolower($description)) {
            case stripos($description, 'conception') !== false && stripos($description, 'machine') !== false:
                return "SIC";
            case stripos($description, 'automatisation') !== false:
                return "EAI";
            case stripos($description, 'lectronique') !== false:
                return "EEM";
            case stripos($description, 'énergétiques') !== false:
                return "EN";
            case stripos($description, 'thermique') !== false:
                return "THI";
            case stripos($description, 'microtechnique') !== false:
                return "MI";
            
        
        // switch (strtolower($department)) {
        //     case strtolower("Technique et IT - Electronique et automatisation industrielle"):
        //         return "EAI";
        //     case strtolower("Technique et IT - Electronique embarquée et Mécatronique"):
        //         return "EEM";
        //     case strtolower("Technique et IT - Systèmes énergétiques"):
        //         return "EN";
        //     case strtolower("Technique et IT - Energie et techniques environnementales"):
		//         return "ETE";
        //     case strtolower("Technique et IT - Energétique du bâtiment"):
        //         return "EBA";
        //     case strtolower("Technique et IT - Thermique industrielle"):
        //         return "THI";
        //     case strtolower("Technique et IT - Thermotronique"):
        //         return "THO";
        //     case strtolower("Technique et IT - Ingnierie et gestion industrielles"):
        //         return "IGIS";
        //     case strtolower("Technique et IT - Logistique et organisation industrielles"):
        //         return "IGLO";
        //     case strtolower("Technique et IT - Qualit et performances industrielles"):
        //         return "IGQP";
        //     case strtolower("Technique et IT - Robotique et conception microtechnique"):
        //         return "MI";
        //     case strtolower("Technique et IT - Conception de machines"):
        //         return "SIC";
        //     case strtolower("Technique et IT - Informatique embarque"):
        //         return "IE";
        //     case strtolower("Technique et IT - Logiciel"):
        //         return "IL";
        //     case strtolower("Technique et IT - Systmes de gestion"):
        //         return "SG";
        //     case strtolower("Technique et IT - Ingnierie des donnes"):
        //         return "ISCD";
        //     case strtolower("Technique et IT - Systmes informatiques embarqus"):
        //         return "ISCE";
        //     case strtolower("Technique et IT - Informatique logicielle"):
        //         return "ISCL";
        //     case strtolower("Technique et IT - Rseaux et systmes"):
        //         return "ISCR";
        //     case strtolower("Technique et IT - Scurit informatique"):
        //         return "ISCS";
        //     case strtolower("Technique et IT - Rseaux et services"):
        //         return "TR";
        //     case strtolower("Technique et IT - Scurit de l'information"):
        //         return "TS";
        //     case strtolower("Economie et services - Economie d'entreprise"):
        //         return "EE";
        //     case strtolower("Technique et IT - Ingnierie des mdias"):
        //         return "IM";
	    //     case strtolower("Informatique et système d'information"):
		//         return "EAI";
        //     case strtolower("Architecture, construction et planification"):
        //         return "!!!";


            // WARNING TODO TMZ : mauvais user dans LDAP
            case "Formation de base":
                Log::error('Formation de base : ' . $this->decodedToken->family_name);
                return "N/A";
            case "Assistant étudiant":
                Log::error('Formation de base : ' . $this->decodedToken->family_name);
                return "N/A";

            default:
                Log::error('Orientation not found : ' . $department . ' / ' . $this->decodedToken->family_name);
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
        $ldapserver = 'ldaps://ldaps.heig-vd.ch:636/';
        $ldapuser = 'iai-ldap';
        $ldappass = env('LDAP_PASSWORD', null);
        $ldaptree = "DC=einet,DC=ad,DC=eivd,DC=ch";
        $ldapconn = ldap_connect($ldapserver) or die("Could not connect to LDAP server.");
	ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
	//ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 1);
        if ($ldapconn) {
	    for($i=0; $i<5; $i++){
                $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass);// or die("Error trying to bind: " . ldap_error($ldapconn));
		if($ldapbind){
		    break;
		}
		else{
		    'Error : ' . $i . ' txt : ' . ldap_error($ldapconn);
		}
	    }
            if ($ldapbind) {
                $filtre = "(|(mail=$user_email*))";
                $restriction = array("cn", "sn", "department", "title", "description");
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
        $user = $this->provider->retrieveByCredentials($credentials);
            //$this->ldap_request($this->decodedToken->email);
	  //  dd("hhh");
        if (is_null($user)) {
            $this->ldap_request($this->decodedToken->email);
	    if (!$this->ldapData){
		    Log::debug('LDAP request error');
		    return false;
	    }
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
	    Log::debug('Check if user exist : '. $user->email);
	    $check = User::where('email', $user->email)->first();
	    if(!$check){
                $user->save();
	    }
	    else{
	        Log::debug('user exist and not saved');
	    }
	}
        $this->setUser($user);

        //if ($user->isStudent() )
        if ($user->isProfessor() )
        {
            dd("Not open");
        }
        
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

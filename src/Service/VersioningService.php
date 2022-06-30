<?php
//Création d'un service Symfony pour pouvoir récup la version contenue dans le champ 'accept' de la requete http
namespace App\Service;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class VersioningService
{
    private $requestStack;

    /**
     * constructeur permettant de récupérer la requête courante (pour extraire le champ "accept"
     * du header) ainsi que ParameterBagInterface pour récup version par défaut dans fichier de config
     * @param RequestStack $requestStack
     * @param ParameterBagInterface $params
     */

    public function __construct(RequestStack $requestStack, ParameterBagInterface $params)
    {
        $this->requestStack = $requestStack;
        $this->defaultVersion = $params->get('default_api_version');
    }
    /**
     * Récup de la version qui a été envoyé dans le header "accept" de la requete http
     * @return string : numéro de la version
     */
    public function getVersion() : string
    {
        $version = $this->defaultVersion;

        $request = $this->requestStack->getCurrentRequest();
        $accept = $request->headers->get('Accept');
        //Récup numéro de version dans la chaine de caractere du accept
        //exemple : 'application/json; test=bidule; version=2.0' => 2.0
        $entete = explode(';', $accept);
        
        //On parcours toutes les entetes pour trouver la version
        foreach ($entete as $value) {
            if(strpos($value, 'version') !== false){
                $version = explode('=', $value);
                $version = $version[1];
                break;
            }
        }
        return $version;
    }
}
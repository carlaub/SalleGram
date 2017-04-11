<?php

namespace pwgram\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class homeController {

    public function renderHome(Application $app, Request $request){
        //TODO comprovar si logejat
        $TotaInfoDeFotos = 0; //TODO Llegir info de la bbdd i pasar un array d'imatges
        return $app['twig']->render('home.twig', array(
            'logged'=>false,
            'data'=>$TotaInfoDeFotos //SERA UN ARRAY
        ));

    }

    public function renderLogin(Application $app, Request $request){
        //TODO comprovar si logejat
        $TotaInfoDeFotos = 0; //TODO Llegir info de la bbdd i pasar un array d'imatges
        return $app['twig']->render('login.twig', array(
            'logged'=>false,
            'data'=>$TotaInfoDeFotos //SERA UN ARRAY
        ));

    }

    public function renderRegistration(Application $app, Request $request){
        //TODO comprovar si logejat
        $TotaInfoDeFotos = 0; //TODO Llegir info de la bbdd i pasar un array d'imatges
        return $app['twig']->render('register.twig', array(
            'logged'=>false,
            'data'=>$TotaInfoDeFotos //SERA UN ARRAY
        ));

    }
}
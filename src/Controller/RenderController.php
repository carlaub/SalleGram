<?php

namespace pwgram\Controller;

use Silex\Application;


class RenderController {

    public function renderHome(Application $app){
        //TODO comprovar si logejat
        $TotaInfoDeFotos = 0; //TODO Llegir info de la bbdd i pasar un array d'imatges
        return $app['twig']->render('home.twig', array(
            'app'=> ['name' => $app['app.name']],
            'logged'=>true,
            'data'=>$TotaInfoDeFotos //SERA UN ARRAY
        ));

    }

    public function renderLogin(Application $app){
        //TODO comprovar si logejat
        $TotaInfoDeFotos = 0; //TODO Llegir info de la bbdd i pasar un array d'imatges
        return $app['twig']->render('login.twig', array(
            'app'=> ['name' => $app['app.name']],
            'logged'=>false,
            'data'=>$TotaInfoDeFotos //SERA UN ARRAY
        ));

    }

    public function renderRegistration(Application $app){
        //TODO comprovar si logejat
        $TotaInfoDeFotos = 0; //TODO Llegir info de la bbdd i pasar un array d'imatges
        return $app['twig']->render('register.twig', array(
            'app'=> ['name' => $app['app.name']],
            'logged'=>false,
            'data'=>$TotaInfoDeFotos //SERA UN ARRAY
        ));

    }

    public function renderValidation(Application $app, $id) {
        return $app['twig']->render('validation.twig', array(
            'id'=> $id
        ));
    }

}
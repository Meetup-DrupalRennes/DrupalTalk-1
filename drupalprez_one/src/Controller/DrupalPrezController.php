<?php


namespace Drupal\drupalprez_one\Controller;


use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class DrupalPrezController extends ControllerBase {

  public function page(){
    return ['#markup' => 'Ceci est une page'];
  }
}

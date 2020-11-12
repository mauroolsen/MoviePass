<?php

namespace Controllers;

use DAO\Database\Response as Response;
use DAO\ShowDAO as ShowDAO;
use DAO\TheaterDAO as TheaterDAO;
use DAO\RoomDAO as RoomDAO;
use DAO\MovieDAO as MovieDAO;
use Models\Show as Show;

class ShowController
{
  private $showDAO;
  private $theaterDAO;
  private $roomDAO;
  private $movieDAO;

  public function __construct()
  {
    $this->showDAO = new ShowDAO();
    $this->theaterDAO = new TheaterDAO();
    $this->roomDAO = new RoomDAO();
    $this->movieDAO = new MovieDAO();
  }

  /* VIEW METHODS */

  public function ShowListView($responses = [])
  {
    $shows = array();

    if ($_SESSION['user'] && $_SESSION['user']->getRole() == 'ADMIN') {
      $shows = $this->showDAO->GetAll();
      require_once(VIEWS_PATH . "/Show/list.php");
    } else {
      return header('Location: ' . FRONT_ROOT);
    }
  }

  public function ShowAddView($responses = [])
  {
    if (!$_SESSION['user'] || $_SESSION['user']->getRole() != 'ADMIN')
      return header('Location: ' . FRONT_ROOT);

    $theaters = array();
    $theaters = $this->theaterDAO->GetAll();

    $rooms = array();
    $rooms = $this->roomDAO->GetAll();

    foreach ($theaters as $theater) {
      $new_rooms = array();
      foreach ($rooms as $room)
        if ($room->getTheater() == $theater->getId()) {
          array_push($new_rooms, $room);
        }
      $theater->setRooms($new_rooms);
    }

    $movies = array();
    $movies = $this->movieDAO->getMoviesOnLocalDB();

    require_once(VIEWS_PATH . "/Show/add.php");
  }

  public function ShowEditView($show_id, $responses = [])
  {
    $show = null;

    if (!$_SESSION['user'] || $_SESSION['user']->getRole() != 'ADMIN')
      return header('Location: ' . FRONT_ROOT);

    $show = $this->showDAO->GetById($show_id);

    $show->setTheater($this->theaterDAO->GetById($show->getTheater()));
    $show->setRoom($this->roomDAO->GetById($show->getRoom()));
    $show->setMovie($this->movieDAO->getMovieOnLocalDBById($show->getMovie()));
    $show->setDate(str_replace(' ', 'T', $show->getDate()));

    $theaters = array();
    $theaters = $this->theaterDAO->GetAll();

    $rooms = array();
    $rooms = $this->roomDAO->GetAll();

    foreach ($theaters as $theater) {
      $new_rooms = array();
      foreach ($rooms as $room)
        if ($room->getTheater() == $theater->getId()) {
          array_push($new_rooms, $room);
        }
      $theater->setRooms($new_rooms);
    }

    $movies = array();
    $movies = $this->movieDAO->getMoviesOnLocalDB();

    require_once(VIEWS_PATH . "/Show/edit.php");
  }

  /* ACTION METHODS */

  public function Add()
  {
    $responses = [];
    $theater = $this->theaterDAO->GetById($_POST['theater']);
    $room = $this->roomDAO->GetById($_POST['room']);
    $movie = $this->movieDAO->getMovieOnLocalDBById($_POST['movie']);

    $date = date("Y-m-d H:i:s", strtotime($_POST['date']));

    $show = new Show(null, $movie, $theater, $room, $_POST['price'], $date);

    $responses = $this->validateShow($show);

    if (empty($responses)) {
      if ($this->showDAO->Add($show))
        array_push($responses, new Response(true, "Función registrada exitosamente."));
      else
        array_push($responses, new Response(false, "Error al registrar la función."));
    }

    $this->ShowAddView($responses);
  }

  public function Edit()
  {
    $responses = [];
    $theater = $this->theaterDAO->GetById($_POST['theater']);
    $room = $this->roomDAO->GetById($_POST['room']);
    $movie = $this->movieDAO->getMovieOnLocalDBById($_POST['movie']);

    $date = date("Y-m-d H:i:s", strtotime($_POST['date']));

    $show = new Show($_POST['show_id'], $movie, $theater, $room, $_POST['price'], $date);

    $responses = $this->validateShow($show, $_POST['show_id']);

    if (empty($responses)) {
      if ($this->showDAO->Edit($show))
        array_push($responses, new Response(true, "Sala registrado exitosamente."));
      else
        array_push($responses, new Response(false, "Error al editar la función."));
    }

    $this->ShowEditView($_POST['show_id'], $responses);
  }

  /* HELPERS */

  private function validateShow(Show $show, $show_id = null)
  {
    $validationResponses = [];

    // Null validators
    if ($show->getTheater() == NULL)
      array_push($validationResponses, new Response(false, "Cine requerido."));
    if ($show->getRoom() == NULL)
      array_push($validationResponses, new Response(false, "Sala requerida."));
    if ($show->getMovie() == NULL)
      array_push($validationResponses, new Response(false, "Película requerida."));
    if ($show->getPrice() == NULL)
      array_push($validationResponses, new Response(false, "Precio de entrada requerido."));
    if ($show->getDate() == NULL)
      array_push($validationResponses, new Response(false, "Fecha y hora requerida."));

    // Busco si hay funciones dentro de ese horario
    $dbShows = $this->showDAO->CheckShowHour($show->getTheater()->getId(), $show->getRoom()->getId(), $show->getDate(), $show->getMovie()->getDuration(), $show_id);

    // Filtro por nombre si el cine tiene salas
    if ($dbShows > 0) {
      array_push($validationResponses, new Response(false, "La sala seleccionada se encuentra ocupada en el horario definido."));
    }

    return $validationResponses;
  }
}

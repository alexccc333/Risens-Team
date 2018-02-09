<?php
class User {
  protected $_mysqli; //connect ot DB
  protected $_id //user's ID in DLE
  protected $_achievements; //current achievements in JSON format

  public function __construct($mysqli) {
    $this->$_mysqli = $mysqli;
  }

  public function setUser($id) {
    /*
      TODO:
      1. Connect to DB
      2. Get user by ID
      3. Set achievements
    */
  }

  public function setAchievements($achiv) {
    $tempArray = json_decode($this->$_achievements);
    $achiv = json_decode($achiv);

    $resultArray = array_merge($tempArray, $achiv);
    foreach ($resultArray as $key => $value) {
      if ($value === -1) {
        unset($resultArray[$key]);
      }
    }

    $this->$_achievements = json_encode($resultArray);
    /*
      TODO:
      Save result to DB
    */
  }
}

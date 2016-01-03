<?php

  Class SC_Weapon extends SC_Item {

    protected $path;
    private $XML;
    private $type = "weapon";
    private $ammo = false;
    private $tchild;
    private $ammoBox = false;

    function __construct($item) {
      parent::__construct($item);
      $this->setPath();

      if($this->OK) {
        $this->XML = $this->XML_OPEN($this->path);
        $this->get_stats($this->XML->params->param);
        $this->getPortMinMaxSize();

        $this->getSubItems();
        $this->getAmmos();
        $this->getAmmoBoxes();

        $this->saveJson("Weapons/".$this->type."/");
      }
    }


    function getSubItems() {

      // SubItems declared by parent
      if(isset($this->raw['Items'])) {
        foreach($this->raw['Items'] as $subitem) {
          $this->createSub((array) $subitem);
        }

      }

      // SubItems declared by self
      $this->rFindSelfSub($this->XML->defaultLoadout);

      if($this->tchild) $this->children = $this->tchild;
    }

    function rFindSelfSub($xml) {
      if($xml && $xml->Items) {
        foreach($xml->Items->Item as $key=>$item) {
          $notAWeap = false;
          try {
            $this->createSub((array) $item);
            $this->rFindSelfSub($item);
          }
          catch(Exception $e) {
            $notAWeap = true;
          }

          if(!$notAWeap) unset($xml->Items->Item->key);
        }
      }
    }

    function createSub($subitem) {
      $sub = new SC_Weapon($subitem);
      $this->tchild[] = $sub->returnHardpoint($subitem['@attributes']['portName']);
    }

    function getAmmoBoxes() {
      $boxes = false;
      if($this->XML->defaultLoadout && $this->XML->defaultLoadout->Items)  {
        foreach($this->XML->defaultLoadout->Items->Item as $ammoBox) {
          $ammoBox = (array) $ammoBox;
          $ex = false;
          try {
            $sub = new SC_Ammo($ammoBox,true);
          }
          catch(Exception $e) {
            $ex = true;
          }
          // If no exception AND the ammo managed to be compiled -TO.DO Spotty ammo exception throwing
          if(!$ex && $sub->isDone()) $boxes[] = $sub->returnHardpoint($ammoBox['@attributes']['portName']);
        }

        if($boxes) $this->ammoBox = $boxes;
      }
    }

    function getAmmos() {
      if(isset($this->XML->ammos)) {
        foreach((array) $this->XML->ammos as $ammo) {
          $ammo = (array) $ammo;
          $sub = new SC_Ammo($ammo);

          $ammos[] = $sub->getInfos();
        }
        if($ammo) $this->ammo = $ammos;
      }
    }

    function returnHardpoint($portName) {
      $ar = parent::returnHardpoint($portName);

      if($this->ammo) $ar['DEFAULT']['AMMO']    = $this->ammo;
      if($this->ammoBox) $ar['DEFAULT']['AMMOBOX'] = $this->ammoBox;

      return $ar;
    }

    function setPath() {
      $paths = ["weapon", "turret", "mount", "missile", "ammo"];
        foreach($paths as $path) {
          if ($this->switchPath($path)) break;
        }

        if($this->path) return true;
        else {
          $this->OK = false;
          throw new Exception("NoMatchingWeapon : ".$this->itemName);
        }
    }

    function switchPath($path) {
      $t = $this->findXML($path, $this->itemName, "Interface");
        if($t) {
          $this->path = $t['file'];
          $this->type = $path;
          return true;
        }
        else return false;
    }

    function getPortMinMaxSize() {
      if($this->XML->portParams && $this->XML->portParams->ports->ItemPort) {
        $port = (array) $this->XML->portParams->ports->ItemPort;
        $this->minSize = $port['@attributes']['minsize'];
        $this->maxSize = $port['@attributes']['maxsize'];
      }
    }



  }
?>

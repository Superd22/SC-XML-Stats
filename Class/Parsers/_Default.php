<?php
  Class SC_xxx extends SC_Item {
    protected $path;

    function __construct($item) {
      parent::__construct($item);

      $this->setPath("xxx","Interface");

      if($this->OK && $this->returnExist($this->path)) {
        $this->XML = simplexml_load_file($this->path);
        $this->setItemMainStats();
        $this->parsexxx();

        $this->saveJson("xxx/");
      }
    }

    function parsexxx() {
      // To do
    }

  }

?>
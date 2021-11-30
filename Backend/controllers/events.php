<?php

class Events extends Controller
{
    public $layout = 'admin';
    
    public $components = ['Auth'];
    
    public $uses = [
        'Event'
    ];

    public function init()
    {
        $this->set("events", "modulo");
        $this->isLogged();
    }
    
    public function list($pag = 1)
    {
        $total = $this->Event->select(['Id'])->where(['IsDeleted' => 0])->count()->run();
        $queryObj = $this->Event->select(['Id', 'EventName', 'EventDate'])->where(['IsDeleted' => 0]);
        $this->set($total, 'total');
        $this->set($pag, 'page');
        $this->set($queryObj->page($pag)->run(), "events");
    }
    
    public function add()
    {
        
    }
    
    public function save()
    {
        $errors = [];
        $eventname = $this->getinput("EventName");
        $description = $this->getinput("Description");
        $eventdate = $this->getinput("EventDate");
        if(is_null($eventname) || empty($eventname))
        {
            $errors["EventName"] = ["isError" => true, "message" => "Ingrese el nombre del evento"];
        }
        if(is_null($eventdate) || empty($eventdate))
        {
            $errors["EventDate"] = ["isError" => true, "message" => "Ingrese la fecha del evento"];
        }
        if($this->haveFiles())
        {
            $eventimage = $this->getFiles("EventImage");
            if($eventimage['size'] > 1024*1024)
            {
                $errors["EventImage"] = ["isError" => true, "message" => "Debe subir una imagen de maximo 1MB"];
            }else{
                $eventimagepath = Configure::read("storagepath")."/eventos/";
                if(!is_dir($eventimagepath))
                {
                    mkdir($eventimagepath);
                }
                if(is_writable($eventimagepath))
                {
                    $eventimagename = "evento_".time().rand(0,9).".".substr($eventimage["name"], strrpos($eventimage["name"], ".") + 1);
                    $eventimagepath .= $eventimagename;
                    if(!move_uploaded_file($eventimage["tmp_name"], $eventimagepath))
                    {
                        $eventimagename = "";
                        $errors["Save"] = ["isError" => true, "message" => "No se pudo subir el archivo"];
                    }
                }else{
                    $errors["Save"] = ["isError" => true, "message" => "No se pudo subir el archivo"];
                }        
            }
        }
        if(empty($errors))
        {
            $rows = $this->Event->insert(['EventName' => $eventname, 'Description' => $description, 'EventDate' => $eventdate, "EventImage" => isset($eventimagename)?$eventimagename:""])->run();
            if(!is_null($rows) && $rows == 1)
            {
                $this->flash(["Save" => ["isError" => false, "message" => "Se grabo exitosamente"]]);
                $this->redirect("/events/list", false);
            }else{
                $this->flash(["Save" => ["isError" => true, "message" => "Hubo un error en la base de datos"]]);
                if(!empty($eventimagename))
                {
                    @unlink($eventimagepath);
                }
                $this->redirect("/events/add");
            }
        }else{
            $this->flash($errors);
            $this->redirect("/events/add");
        }
    }    
    
    public function edit($id)
    {
        if(is_numeric($id))
        {
            $event = $this->Event->select()->where(['Id' => $id])->first();
            if($event instanceof Event )
            {
                $this->set($event, "event");
            }else{
                $this->redirect("/evnts/list");
            }
        }else{
            $this->redirect("/events/list");
        }
    }
    
    public function update($id)
    {
        if(is_numeric($id))
        {
            $errors = [];
            $eventname = $this->getinput("EventName");
            $description = $this->getinput("Description");
            $eventdate = $this->getinput("EventDate");
            $actualimage = $this->getinput("ActualImage");
            if(is_null($eventname) || empty($eventname))
            {
                $errors["EventName"] = ["isError" => true, "message" => "Ingrese el nombre del evento"];
            }
            if(is_null($eventdate) || empty($eventdate))
            {
                $errors["EventDate"] = ["isError" => true, "message" => "Ingrese la fecha del evento"];
            }
            $updateFields = [
                'EventName' => $eventname,
                'Description' => $description,
                'EventDate' => $eventdate,
                'UpdateTime' => date("Y-m-d H:i:s")
            ];
            $deleteOldFile = false;
            if($this->haveFiles())
            {
                $eventimage = $this->getFiles("EventImage");
                if($eventimage['size'] > 1024*1024)
                {
                    $errors["EventImage"] = ["isError" => true, "message" => "Debe subir una imagen de maximo 1MB"];
                }else{
                    $eventimagepath = Configure::read("storagepath")."/eventos/";
                    if(!is_dir($eventimagepath))
                    {
                        mkdir($eventimagepath);
                    }
                    if(is_writable($eventimagepath))
                    {
                        $eventimagename = "evento_".time().rand(0,9).".".substr($eventimage["name"], strrpos($eventimage["name"], ".") + 1);
                        $eventimagepath .= $eventimagename;
                        if(!move_uploaded_file($eventimage["tmp_name"], $eventimagepath))
                        {
                            $eventimagename = "";
                            $errors["Save"] = ["isError" => true, "message" => "No se pudo subir el archivo"];
                        }else{
                            $deleteOldFile = true;
                            $updateFields["EventImage"] = $eventimagename;
                        }
                    }else{
                        $errors["Save"] = ["isError" => true, "message" => "No se pudo subir el archivo"];
                    }    
                }
            }
            if(empty($errors))
            {
                $rows = $this->Event->update($updateFields)->where(['Id' =>$id])->run();
                if(!is_null($rows) && $rows == 1)
                {
                    $this->flash(["Save" => ["isError" => false, "message" => "Se grabo exitosamente"]]);
                    if($deleteOldFile && !empty($actualimage) && is_file($eventimagepath.$actualimage))
                    {
                        @unlink($eventimagepath.$actualimage);
                    }
                    $this->redirect("/events/list", false);
                }else{
                    $this->flash(["Save" => ["isError" => true, "message" => "Hubo un error en la base de datos"]]);
                    if(!empty($eventimagename))
                    {
                        @unlink($eventimagepath);
                    }
                    $this->redirect("/events/edit/".$id);
                }
            }else{
                $this->flash($errors);
                $this->redirect("/events/edit/".$id);
            }
        }else{
            $this->redirect("/events/list");
        }
    }

    public function delete($id)
    {
        if(is_numeric($id))
        {
            $rows = $this->Event->Update(['IsDeleted' => 1, 'UpdateTime' => date("Y-m-d H:i:s")])->where(['Id' => $id])->run();
            if(!is_null($rows) && $rows == 1)
            {
                $this->flash(["Save" => ["isError" => false, "message" => "Se elimino exitosamente"]]);
            }else{
                $this->flash(["Save" => ["isError" => true, "message" => "Hubo un error en la base de datos"]]);
            }
        }
        $this->redirect("/events/list");
    }
    
    private function isLogged()
    {
        if(!$this->Auth->verify())
        {
            $this->redirect("/logout");
        }
    }
}

?>

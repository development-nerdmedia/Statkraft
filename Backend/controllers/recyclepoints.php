<?php

class RecyclePoints extends Controller
{
    public $layout = 'admin';
    
    public $components = ['Auth'];
    
    public $uses = [
        'RecyclePoint',
        'RecycleOption',
        'Ubigeo'
    ];

    public function init()
    {
        $this->set("recyclepoints", "modulo");
        $this->isLogged();
    }
    
    public function list($pag = 1)
    {
        $total = $this->RecyclePoint->select(['Id'])->where(['IsDeleted' => 0])->count()->run();
        $this->set($total, 'total');
        $this->set($pag, 'page');
        $queryObj = $this->RecyclePoint->select(['Id', 'PointName'])->where(['IsDeleted' => 0]);
        $this->set($queryObj->page($pag)->run(), "recyclepoints");
    }
    
    
    public function add()
    {
        $queryObj = $this->RecycleOption->select(['Id', 'OptionName']);
        $this->set($queryObj->run(), "recycleoptions");
        $queryObjUbigeo = $this->Ubigeo->select(['Id', 'Name'])->where(['ParentId' => 0]);
        $this->set($queryObjUbigeo->run(), "departamentos");

    }
    
    public function save()
    {
        $errors = [];
        $pointname = $this->getinput("PointName");
        $address = $this->getinput("Address");
        $telephone = $this->getinput("Telephone");
        $hours = $this->getinput("Hours");
        $coordinatey = $this->getinput("CoordinateY");
        $coordinatex = $this->getinput("CoordinateX");
        $ubigeoid = $this->getinput("UbigeoId");
        if(is_null($pointname) || empty($pointname))
        {
            $errors["PointName"] = ["isError" => true, "message" => "Ingrese el nombre"];
        }
        if(is_null($ubigeoid) || empty($ubigeoid) || $ubigeoid < 1)
        {
            $errors["UbigeoId"] = ["isError" => true, "message" => "Seleccione un distrito"];
        }
        if(!$this->haveFiles())
        {
            $errors["Thumbnail"] = ["isError" => true, "message" => "Debe subir una imagen"];
        }else{
            $thumbnail = $this->getFiles("Thumbnail");
            if($thumbnail['size'] > 1024*1024)
            {
                $errors["Thumbnail"] = ["isError" => true, "message" => "Debe subir una imagen de maximo 1MB"];
            }    
        }
        if(empty($errors))
        {
            $thumbnailpath = Configure::read("storagepath")."/puntos/";
            if(!is_dir($thumbnailpath))
            {
                mkdir($thumbnailpath);
            }
            if(is_writable($thumbnailpath))
            {
                $thumbnailname = "puntoreciclaje_".time().rand(0,9).".".substr($thumbnail["name"], strrpos($thumbnail["name"], ".") + 1);
                $thumbnailpath .= $thumbnailname;
                if(move_uploaded_file($thumbnail["tmp_name"], $thumbnailpath))
                {
                    $rows = $this->RecyclePoint->insert([
                        'PointName' => $pointname,
                        'Address' => $address,
                        'Telephone' => $telephone,
                        'Hours' => $hours,
                        'Latitude' => $coordinatey,
                        'Longitude' => $coordinatex,
                        'UbigeoId' => $ubigeoid,
                        'Thumbnail' => $thumbnailname
                                ])->run();
                    if(!is_null($rows) && $rows == 1)
                    {
                        $recyclePointId = $this->RecyclePoint->getLastId();
                        $options = $this->getinput("RecycleOption");
                        if(is_array($options))
                        {
                            foreach($options as $option)
                            {
                                $this->RecyclePoint->insert()->run('INSERT INTO '.Configure::read("DB.default.prefix").'RECYCLE_POINT_OPTIONS (PointId, OptionId) values ('.$recyclePointId.','.intval($option).')');
                            }
                        }
                        $this->flash(["Save" => ["isError" => false, "message" => "Se grabo exitosamente"]]);
                        $this->redirect("/recyclepoints/list");
                    }else{
                        $this->flash(["Save" => ["isError" => true, "message" => "Hubo un error en la base de datos"]]);
                        unlink($thumbnailpath);
                        $this->redirect("/recyclepoints/add");
                    }
                }else{
                    $errors["Save"] = ["isError" => true, "message" => "No se pudo subir el archivo"];
                }
            }else{
                $errors["Save"] = ["isError" => true, "message" => "No se pudo subir el archivo"];
            }
        }else{
            $this->flash($errors);
            $this->redirect("/recyclepoints/add");
        }
    }
    
    public function edit($id)
    {
        if(is_numeric($id))
        {
            $recyclepoint = $this->RecyclePoint->select()->where(['Id' => $id])->first();
            if($recyclepoint instanceof RecyclePoint )
            {
                $ubigeo = $this->Ubigeo->select(['ParentId'])->where(['Id' => $recyclepoint->UbigeoId])->first();                
                $queryDist = $this->Ubigeo->select(['Id', 'Name'])->where(['ParentId' => $ubigeo->ParentId]);
                $this->set($queryDist->run(), "distritos");
                $ubigeoprov = $this->Ubigeo->select(['ParentId'])->where(['Id' => $ubigeo->ParentId])->first();
                $this->set($ubigeo->ParentId, 'selectedprov');
                $queryProv = $this->Ubigeo->select(['Id', 'Name'])->where(['ParentId' => $ubigeoprov->ParentId]);
                $this->set($queryProv->run(),"provincias");
                $this->set($ubigeoprov->ParentId, 'selecteddept');
                $this->set($recyclepoint, "recyclepoint");
                $queryObj = $this->RecycleOption->select(['Id', 'OptionName']);
                $this->set($queryObj->run(), "recycleoptions");
                $queryObjUbigeo = $this->Ubigeo->select(['Id', 'Name'])->where(['ParentId' => 0]);
                $queryOptions = $this->RecyclePoint->select()->run('SELECT OptionId FROM '.Configure::read("DB.default.prefix").'RECYCLE_POINT_OPTIONS WHERE PointId='.$recyclepoint->Id);
                $this->set(array_map(function ($element) { return $element->OptionId; }, $queryOptions), "selectedOptions");
                $this->set($queryObjUbigeo->run(), "departamentos");
            }else{
                $this->redirect("/recyclepoints/list");
            }
        }else{
            $this->redirect("/recyclepoints/list");
        }
    }
    
    public function update($id)
    {
        if(is_numeric($id))
        {
            $errors = [];
            $pointname = $this->getinput("PointName");
            $address = $this->getinput("Address");
            $telephone = $this->getinput("Telephone");
            $hours = $this->getinput("Hours");
            $coordinatey = $this->getinput("CoordinateY");
            $coordinatex = $this->getinput("CoordinateX");
            $ubigeoid = $this->getinput("UbigeoId");
            $actualimage = $this->getinput("ActualImage");
            if(is_null($pointname) || empty($pointname))
            {
                $errors["PointName"] = ["isError" => true, "message" => "Ingrese el nombre"];
            }
            if(is_null($ubigeoid) || empty($ubigeoid) || $ubigeoid < 1)
            {
                $errors["UbigeoId"] = ["isError" => true, "message" => "Seleccione un distrito"];
            }
            $updateFields = [
                'PointName' => $pointname,
                'Address' => $address,
                'Telephone' => $telephone,
                'Hours' => $hours,
                'Latitude' => $coordinatey,
                'Longitude' => $coordinatex,
                'UbigeoId' => $ubigeoid,
                'UpdateTime' => date("Y-m-d H:i:s")
            ];
            $deleteOldFile = false;
            if($this->haveFiles())
            {
                $thumbnailpath = Configure::read("storagepath")."/puntos/";
                $thumbnail = $this->getFiles("Thumbnail");
                if($thumbnail['size'] > 1024*1024)
                {
                    $errors["Save"] = ["isError" => true, "message" => "Debe subir una imagen de maximo 1MB"];
                }else{
                    if(is_writable($thumbnailpath))
                    {
                        $thumbnailname = "puntoreciclaje_".time().rand(0,9).".".substr($thumbnail["name"], strrpos($thumbnail["name"], ".") + 1);
                        $thumbnailpath .= $thumbnailname;
                            if(move_uploaded_file($thumbnail["tmp_name"], $thumbnailpath))
                        {
                            $updateFields["Thumbnail"] = $thumbnailname;
                            $deleteOldFile = true;
                        }else{
                            $errors["Save"] = ["isError" => true, "message" => "No se pudo subir el archivo"];                            
                        }
                    }else{
                        echo "Here";
                        $errors["Save"] = ["isError" => true, "message" => "No se pudo subir el archivo"];
                    }    
                }
            }
            if(empty($errors))
            {
                $rows = $this->RecyclePoint->update($updateFields)->where(['Id'=>$id])->run();
                if(!is_null($rows) && $rows == 1)
                {
                    $options = $this->getinput("RecycleOption");
                    $this->RecyclePoint->delete()->run('DELETE FROM '.Configure::read("DB.default.prefix").'RECYCLE_POINT_OPTIONS Where PointId = '.$id);
                    if(is_array($options))
                    {
                        foreach($options as $option)
                        {
                            $this->RecyclePoint->insert()->run('INSERT INTO '.Configure::read("DB.default.prefix").'RECYCLE_POINT_OPTIONS (PointId, OptionId) values ('.$id.','.intval($option).')');
                        }
                    }
                    $this->flash(["Save" => ["isError" => false, "message" => "Se grabo exitosamente"]]);
                    if($deleteOldFile)
                    {
                        unlink($thumbnailpath.$actualimage);
                    }
                    $this->redirect("/recyclepoints/list");
                }else{
                    $this->flash(["Save" => ["isError" => true, "message" => "Hubo un error en la base de datos"]]);
                    if($this->haveFiles())
                    {
                        unlink($thumbnailpath);
                    }
                    $this->redirect("/recyclepoints/edit/".$id);
                }
            }else{
                $this->flash($errors);
                $this->redirect("/recyclepoints/edit/".$id);
            }       }else{
            $this->redirect("/recyclepoints/list");
        }
    }
    
    public function delete($id)
    {
        if(is_numeric($id))
        {
            $rows = $this->RecyclePoint->update(['IsDeleted' => 1, 'UpdateTime' => date("Y-m-d H:i:s")])->where(['Id' => $id])->run();            
            if(!is_null($rows) && $rows == 1)
            {
                $this->flash(["Save" => ["isError" => false, "message" => "Se elimino exitosamente"]]);
            }else{
                $this->flash(["Save" => ["isError" => true, "message" => "Hubo un error en la base de datos"]]);
            }
        }
        $this->redirect("/recyclepoints/list");
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

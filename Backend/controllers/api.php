<?php

class Api extends Controller
{
    public $uses = [
        'Ubigeo',
        'Newsletter',
        'RecyclePoint',
        'Event'
    ];
    
    public $layout = '';

    public $output = 'json';

    private $response;

    public function __construct()
    {
        parent::__construct();
        $this->response = new stdClass();
    }

    public function GetUbigeoByParentId($parentId = 0)
    {
        $parentId = intval(($parentId));
        if($parentId >= 0)
        {
            $this->set($this->Ubigeo->select(['Id', 'Name'])->where(['ParentId' => $parentId])->run(), "ubigeos");
        }
    }

    public function getRecyclePointList(int $page = 1, int $limit = 6, int $departamento = 0,int $provincia = 0, $distrito = 0)
    {
        if($page < 1)
        {
            $page = 1;
        }
        if($limit < 1)
        {
            $limit = 6;
        }        
        try{
            $queryResults = $this->RecyclePoint->select(['Id', 'PointName', 'Thumbnail', "Address", "Telephone", "Hours", "Latitude", "Longitude"], ['UD.Name as Departamento']);
            $queryResults->join($this->Ubigeo, ['type' => 'inner', 'field1' => ['UbigeoId'], 'field2' => ['Id']], 'U');
            $queryResults->join($this->Ubigeo, ['type' => 'inner', 'field1' => ['ParentId'], 'field2' => ['Id']], 'U', $this->Ubigeo, 'UP');
            $queryResults->join($this->Ubigeo, ['type' => 'inner', 'field1' => ['ParentId'], 'field2' => ['Id']], 'UP', $this->Ubigeo, 'UD');
            if($departamento > 0 && $provincia == 0 && $distrito == 0)
            {
                $provincias = $this->Ubigeo->select(['Id'])->where(['ParentId' => $departamento])->run();
                $includedDistritos = [];
                foreach($provincias as $provioncialoop)
                {
                    $dsitritos = $this->Ubigeo->select(['Id'])->where(['ParentId' => $provioncialoop->Id])->run();
                    foreach($dsitritos as $distritoloop)
                    {
                        $includedDistritos[] = $distritoloop->Id;
                    }    
                }
                if(count($includedDistritos) > 0)
                {
                    $queryResults->where(["UbigeoId" => $includedDistritos]);
                }
            }
            if($provincia > 0 && $distrito == 0)
            {
                $dsitritos = $this->Ubigeo->select(['Id'])->where(['ParentId' => $provincia])->run();
                $includedDistritos = [];
                foreach($dsitritos as $distritoloop)
                {
                    $includedDistritos[] = $distritoloop->Id;
                }
                if(count($includedDistritos) > 0)
                {
                    $queryResults->where(["UbigeoId" => $includedDistritos]);
                }
            }
            if($distrito > 0)
            {
                $queryResults->where(["UbigeoId" => $distrito]);
            }
            $queryResults->where(['IsDeleted' => 0]);
            $queryResultsTotal = clone $queryResults;
            $queryResults->limit($limit);
            $queryResults->page($page);
            $results = $queryResults->run();
            foreach($results as $result)
            {
                $options = $this->RecyclePoint->select()->run('SELECT OptionId FROM '.Configure::read("DB.default.prefix").'RECYCLE_POINT_OPTIONS WHERE PointId='.$result->Id);                
                $result->Options = array_map(function ($element) { return $element->OptionId; }, $options);
            }
            $this->response->IsError = false;
            $this->response->ErrorMsg = "";
            $this->response->results = $results;
            $this->response->total = $queryResultsTotal->count()->run();
        }catch(Exception $e){
            $this->response->IsError = true;
            $this->response->ErrorMsg = "DB_ERROR";
        }
        $this->set($this->response);
    }

    public function getRecyclePointById(int $id)
    {

        if($id < 1)
        {
            $this->response->IsError = true;
            $this->response->ErrorMsg = "INVALID_ID";
        }else{
            try{
                $queryResults = $this->RecyclePoint->select(['Id', 'PointName', 'Thumbnail', "Address", "Telephone", "Hours", "Latitude", "Longitude"], ['UD.Name as Departamento']);
                $queryResults->join($this->Ubigeo, ['type' => 'inner', 'field1' => ['UbigeoId'], 'field2' => ['Id']], 'U');
                $queryResults->join($this->Ubigeo, ['type' => 'inner', 'field1' => ['ParentId'], 'field2' => ['Id']], 'U', $this->Ubigeo, 'UP');
                $queryResults->join($this->Ubigeo, ['type' => 'inner', 'field1' => ['ParentId'], 'field2' => ['Id']], 'UP', $this->Ubigeo, 'UD');
                $queryResults->where(['IsDeleted' => 0, 'RecyclePoint.Id' => $id]);
                $results = $queryResults->first();
                if(is_object($results))
                {
                    $options = $this->RecyclePoint->select()->run('SELECT OptionId FROM '.Configure::read("DB.default.prefix").'RECYCLE_POINT_OPTIONS WHERE PointId='.$results->Id);
                    $results->Options = array_map(function ($element) { return $element->OptionId; }, $options);
                }else{
                    $result = new stdClass();
                    $result->Options = [];
                }
                $this->response->IsError = false;
                $this->response->ErrorMsg = "";
                $this->response->results = $results;
            }catch(Exception $e){
                $this->response->IsError = true;
                $this->response->ErrorMsg = "DB_ERROR";
            }
        }
        $this->set($this->response);
    }

    public function getEventList(int $year = 0, int $month = 0)
    {
        $months = [
            1 => "Enero",
            2 => "Febrero",
            3 => "Marzo",
            4 => "Abril",
            5 => "Mayo",
            6 => "Junio",
            7 => "Julio",
            8 => "Agosto",
            9 => "Septiembre",
            10 => "Octubre",
            11 => "Noviembre",
            12 => "Diciembre"
        ];
        if($month < 0 || $month > 12)
        {
            $this->response->IsError = true;
            $this->response->ErrorMsg = "INVALID_MONTH";
        }else{
            try{
                $queryObj = $this->Event->select(["Id", "EventName", "Description", "EventDate", "EventImage"])->where(["IsDeleted" => 0]);
                if($year != 0){
                    $queryObj->where(["YEAR(Event.EventDate) = ".$year]);
                    if($month > 0){
                        $queryObj->where(["MONTH(Event.EventDate) = ".$month]);
                    }                
                }
                $results = $queryObj->order(["EventDate asc"])->run();
                foreach($results as $result)
                {
                    $result->month = date("m", strtotime($result->EventDate));
                    $result->monthtext = $months[date("n", strtotime($result->EventDate))];
                    $result->year = date("Y", strtotime($result->EventDate));
                }
                $this->response->IsError = false;
                $this->response->ErrorMsg = "";
                $this->response->results = $results;
            }catch(Exception $e){
                $this->response->IsError = true;
                $this->response->ErrorMsg = "DB_ERROR";
            }
        }
        $this->set($this->response);
    }
            
    public function saveNewsletterEmail()
    {
        $email = $this->getinput("Email");
        if(empty($email))
        {
            $this->response->IsError = true;
            $this->response->ErrorMsg = "EMTPY_EMAIL";
        }else{
            $rows = $this->Newsletter->insert(['Email' => $email])->run();
            if(!is_null($rows) && $rows == 1)
            {
                $this->response->IsError = false;
                $this->response->ErrorMsg = "";
            }else{
                $this->response->IsError = true;
                $this->response->ErrorMsg = "DB_SAVE_FAIL";    
            }
        }
        $this->set($this->response);
    }


}

?>

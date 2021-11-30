<?php 

class RecyclePoint extends Model
{
	protected $table = 'RECYCLE_POINTS';
	
	public $Id;
	
	public $PointName;
	
	public $Address;

	public $Telephone;

	public $Hours;

	public $Latitude;

	public $Longitude;

	public $UbigeoId;

	public $Thumbnail;

}

?>
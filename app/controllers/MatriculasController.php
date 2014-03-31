<?php

class MatriculasController extends BaseController
{
	public $errors;
	public $_matricula;

	public function __construct()
	{
		$this->_matricula = new Matriculas();
		
	}

	public function MatriculaAlum(){
		
		$tipos = Tipodoc::all()->lists('name_tipodoc','id_tipodoc');
		$grados = Grado::all()->lists('nombre','id');
		$month = date("m");
		$year;
		if ($month > 7) {
			$year=date('Y')+1;
		}
		elseif ($month < 8) {
			$year=date('Y');
		}
		$lastY = $year - 1;
		$nextY = $year + 1;

		$años = array("year"=>$year,"lastY"=>$lastY,"nextY"=>$nextY);
		return View::Make('matriculas.alum')->with(array('años' => $años,'tipodoc'=>$tipos,'grado'=>$grados ));
	}

	public function nuevo(){
		if(Input::get())
		{
			$data = Input::all();			
			$reglas = array(
				'alum'					=>	'required',
				'fname'					=>	'required',
				'year_matricula'		=>	'required',
				'genero'				=>	'required',
				'grado'					=>	'required',
				'T-reg'					=>  'required'
			);
			$mensajes = array(
					'alum.required'			 	=> 'Digite el nombre del alumno.',
					'fname.required' 			=> 'Digite el apellido del alumno.',
					'year_matricula.required' 	=> 'Seleccione un año Escolar.',
					'genero.required' 			=> 'Seleccione un genero.',					
					'grado.required' 			=> 'Seleccione un grado.',				
					'T-reg.required' 			=> 'Seleccione Inscripcion o Matricula.'				
			);

			if (Input::get('T-reg')==1) 
			{
				$reglas['fecha_matricula'] = 'required';
				$mensajes['fecha_matricula.required'] = 'Seleccione la fecha de la matricula.';
				$tabla = $this->asignTabla($data['year_matricula']);
				$codigoMatri = $this->_matricula->cod_matri($tabla);
				$codigoMatri = $codigoMatri +1;
				$validacion = Validator::make($data,$reglas,$mensajes);
				
				if ($validacion->fails())
				{
					return Redirect::to('matriculas/')->withInput()->withErrors($validacion)->with(array('datos'=>$data));
				}
				else
				{
					$tipos = Tipodoc::all()->lists('name_tipodoc','id_tipodoc');
					$tabla = $this->asignTabla($data['year_matricula']);
					$data['codigoMatri']=$codigoMatri;
					//$save = $this->_matricula->saveMatricula($data,$tabla);
					//print_r($data);
					if (!empty($data['id_alum'])) {	
						return View::make('matriculas.info-complementaria')->with(array('id_alum'=>$data['id_alum'],'tipoR'=>$data['T-reg'],'name'=>$data['alum'],'codM'=>$data['codigoMatri']));
					}
					else{
						return View::make('matriculas.info-complementaria')->with(array('tipoR'=>$data['T-reg'],'name'=>$data['alum'],'codM'=>$data['codigoMatri']));
					}
				}
			}
			if (Input::get('T-reg')==0) 
			{
				$validacion = Validator::make($data,$reglas,$mensajes);
				if ($validacion->fails()) 
				{
					return View::make('matriculas/')->withInput()->withErrors($validacion)->with(array('datos'=>$data));
				}

				else
				{
					$tipos = Tipodoc::all()->lists('name_tipodoc','id_tipodoc');
					$tabla = $this->asignTabla($data['year_matricula']);
					//$save = $this->_matricula->saveInscripcion($data,$tabla);
					
					print_r($data);
					
					
					if (!empty($data['id_alum'])) {	
						//return View::make('matriculas.info-complementaria')->with(array('id_alum'=>$data['id_alum'],'tipoR'=>$data['T-reg'],'name'=>$data['alum'],'year'=>$data['year_matricula']));
					}
					else{
						//return View::make('matriculas.info-complementaria')->with(array('tipoR'=>$data['T-reg'],'name'=>$data['alum']));
					}
				}
			}			
		}
	}

	public function searchalum($year){
		$tabla = $this->asignTabla($year);
		if(Input::get('term'))
		{
			$found = $this->_matricula->autoCompletename(Input::get('term'),$year,$tabla);
			return Response::json($found);
		}
	}

	public function asignTabla($year){
			switch ($year)
			{
	    		case ($year = date('Y'))&&(date('m')<=7):
			        $tablaAlumnos = "alumnos_last";
			        break;
	    		case $year < date('Y'):
			        $tablaAlumnos = "alumnos_fecha";
			        break;
	    		case ($year > date('Y'))&&(date('m')>=8):
			        $tablaAlumnos = "alumnos";
			        break;
			}
			return $tablaAlumnos;
		}

	public function srch_papa(){
		if(Input::get('term'))
		{
			$found = $this->_matricula->autoCompleteP(Input::get('term'));
			return Response::json($found);
		}
	}

	public function srch_acudiente(){
		if(Input::get('term'))
		{
			$found = $this->_matricula->autoCompleteA(Input::get('term'));
			return Response::json($found);
		}
	}	

	public function savePadre(){
		$data = Input::all();
		$tipos = Tipodoc::all()->lists('name_tipodoc','id_tipodoc');
		if (!empty($data['datosp'])) {
			$save = $this->_matricula->UpdatePadre($data);
		}

		else{
			$save = $this->_matricula->SavePadre($data);
		}


		}
	
	public function padres($id,$year,$tipoP){
		$tipos = Tipodoc::all()->lists('name_tipodoc','id_tipodoc');
		if (!empty($id)) {
			$tabla = $this->asignTabla($year);
			$papa = $this->_matricula->srch_Id_Papa($tabla,$id);
			$papa = get_object_vars($papa[0]);
			$id_papa=$papa['id_padre'];
			$datosp=$this->_matricula->srch_Papa($id_papa);
			$datosp=get_object_vars($datosp);
			return View::Make('matriculas.padre')->with(array('tipodoc'=>$tipos,'papa'=>$datosp));
		}else{
			//Consultar Id alumno y luego el mismo procedimiento de arriba
		}
	}
	public function acudientes(){
		$tipos = Tipodoc::all()->lists('name_tipodoc','id_tipodoc');
		return View::Make('matriculas.acudiente')->with(array('tipodoc'=>$tipos));
	}

	}
?>
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * @author 		Yusuf Ayuba
	 * @since   	2016
	 */

	public function index()
	{
		$this->add();
	}

	public function add()
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('alumni_model','alumni');
		$data['nama'] = '';
		$this->form_validation->set_rules('nama','Nama','trim|required');
		$this->form_validation->set_rules('wilayah','Wilayah Tinggal','trim|required');
		if ($this->form_validation->run()===FALSE) {
			$this->load->view('form_add',$data);
		} else {
			$nama = $this->input->post('nama');
			$wilayah = $this->input->post('wilayah');
			$data = array(
							'nm_alumni'=>$this->input->post('nama'),
							'id_wil' => $this->input->post('wilayah')
						);
			$this->alumni->create($data);
			$this->session->set_flashdata('message','Sukses, Data alumni berhasil ditambahkan');
			redirect('welcome','refresh');
		}
	}

	public function ajax()
	{
		$this->load->model('alumni_model','alumni');
		$cari = $this->input->post('cari');
		$limit =$this->input->post('page')==''?1:$this->input->post('page');
		$temp = $this->alumni->get_wil_ajax($cari,$limit)->result_array();
		echo json_encode($temp);
	}

	public function maps()
	{
		$this->load->model('alumni_model','alumni');
		$config = array();
		$config['center'] = 'jakarta';
		$config['zoom'] = 'auto';
		$this->googlemaps->initialize($config);

		$temp_result = $this->alumni->get_location()->result();
		$marker = array();
		foreach ($temp_result as $value) {
			$marker['position'] = $value->desa.', '.$value->prop;

			// parameter onclick digunakan untuk mennampilkan popuo windows dalam bentuk modals
			$marker['onclick'] = 'javascript:showModal(\''.$value->id_wil.'\')';
			//$marker['infowindow_content'] = ''.$value->nm_alumni.'<br>Lokasi: '.$value->desa.', '.$value->prop.'';
			$marker['title'] = $value->desa.', '.$value->prop;
			$this->googlemaps->add_marker($marker);	
		}

		$data['map'] = $this->googlemaps->create_map();
		$this->load->view('maps_view', $data);
	}

	public function modal($id)
	{
		$this->load->model('alumni_model','alumni');
		$temp_result = $this->alumni->get_location_by_id($id)->result();
		$temp_place = $this->alumni->get_location_by_id($id)->row();
		$temp_jml = count($temp_result);
		echo "<div class=\"panel panel-primary\">
				<div class=\"panel-heading\">
					<div class=\"row\">
						<div class=\"col-xs-3\">
							<i class=\"fa fa-map-o fa-5x\"></i>
						</div>
						<div class=\"col-xs-9 text-right\">
							<div style=\"font-size: 40px;\">".$temp_jml."</div>
							<div>Data Alumni</div>								
						</div>
						<div class=\"col-xs-12\">
							<hr style=\"margin-bottom: 5px;\" />
							<small>".$temp_place->lokasi."</small>
						</div>
					</div>
				</div>
				<div class=\"panel-footer\">";
					foreach ($temp_result as $value) {
						echo "<div><i class=\"fa fa-user\"></i> ".$value->nm_alumni."</div><hr />";
					}
			echo "</div>
			</div>";
	}
}

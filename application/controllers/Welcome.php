<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->load->view('home');
	}


    public function contactmail()
    {
        $subjet = $this->input->post('InputName');
        $mail = $this->input->post('InputEmail');
        $massage = $this->input->post('InputMessage');
        //var_dump($massage);die;

        $this->load->library('email');
        $config['protocol'] = $this->config->item('protocol');
        $config['smtp_host'] = $this->config->item('smtp_host');
        $config['smtp_port'] = $this->config->item('smtp_port');
        $config['smtp_timeout'] = $this->config->item('smtp_timeout');
        $config['smtp_user'] = $this->config->item('smtp_user');
        $config['smtp_pass'] = $this->config->item('smtp_pass');
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        $config['wordwrap'] = TRUE;
        $config['mailtype'] = 'html';
        $this->email->initialize($config);

        $this->email->from('your@example.com', 'Your Name');
        $this->email->to($mail);

        $this->email->subject($subjet);
        $this->email->message($massage);

        $this->email->send();
    }
}

<?php
class ControllerModulewebengage extends Controller
{
	private $error = array();
    public function install() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "webengage_track` (
		  `u_id` int(11) NOT NULL AUTO_INCREMENT,
		  `product_id` int(11) ,
		  `key` text,
		  `qty` text,
		  `option` text,
		  `recurring_id` int(11),
		  `url` text,
		  PRIMARY KEY (`u_id`)
		)");

		  
 
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "comingsoon_description` (
		  `comingsoon_description_id` int(11) NOT NULL AUTO_INCREMENT,
		  `comingsoon_id` int(11) NOT NULL,
		  `language_id` int(11) NOT NULL,
		  `title` varchar(255) COLLATE utf8_bin NOT NULL,
		  `description` text COLLATE utf8_bin NOT NULL,
		  PRIMARY KEY (`comingsoon_description_id`)
		)");
	}
	public function index()
	{
		$this->language->load('module/webengage');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');

		$webengage_settings = $this->model_setting_setting->getSetting('webengage');
		if(!isset($webengage_settings['webengage_module']) || count($webengage_settings['webengage_module']) <= 0) {
			for ($i=1;$i<=11;$i++) {
				$settings['webengage_module'][] = Array (
					'layout_id' => $i,
					'position' => 'content_bottom',
					'webengage_status' => 1,
					'sort_order' => ''
				);
			}
			$this->model_setting_setting->editSetting('webengage', $settings);
		}

		$this->document->addStyle('./view/stylesheet/webengage_main.css');

		if (!isset($_GET['page']) || strlen(trim($_GET['page'])) <= 0) {
			$this->handleMainPage();
		}
		else {
			switch (trim($_GET['page']))
			{
				case 'main':
					$this->handleMainPage();
					break;

				case 'callback':
					$this->handleCallbackPage();
					break;
			}
		}
	}

	private function getUrl($queryParams) 
	{
		$url = $this->url->link('module/webengage', 'token=' . $this->session->data['token'] . '&' . $queryParams, 'SSL');
		return str_replace('&amp;', '&', $url);
	}

	private function handleMainPage()
	{
		$this->webengageProcessWebengageOptions();

		$webengage_settings = $this->model_setting_setting->getSetting('webengage');

		$webengage_host_name = 'webengage.com';
		$m_webengage_license_code = isset($webengage_settings['webengage_license_code']) ? $webengage_settings['webengage_license_code'] : '';

		$m_webengage_status = isset($webengage_settings['webengage_status']) ? $webengage_settings['webengage_status'] : '';

		$main_url = $this->getUrl('page=main');
		$next_url = $this->getUrl('page=callback&noheader=true');
		$activation_url = $this->getUrl('weAction=activate');

		$data['m_license_code_old'] = $m_webengage_license_code;
		$data['m_widget_webengage_status'] = $m_webengage_status;

		$data['message'] = urldecode(isset($_GET['message']) ? htmlspecialchars($_GET['message'], ENT_COMPAT, 'UTF-8') : '');
		$data['error_message'] = urldecode(isset($_GET['error-message']) ? htmlspecialchars($_GET['error-message'], ENT_COMPAT, 'UTF-8') : '');

		$data['email'] = '';
		$data['user_full_name'] = '';

		$data['main_url'] = $main_url;
		$data['next_url'] = $next_url;
		$data['activation_url'] = $activation_url;

		$data['domain_name'] = '';
		if (isset($_SERVER['HTTP_HOST']))
			$data['domain_name'] = $_SERVER['HTTP_HOST'];
		else
			$data['domain_name'] = $_SERVER['SERVER_NAME'];

		$data['webengage_host_name'] = $webengage_host_name;
		$data['webengage_host'] = 'http://'.$webengage_host_name;
		$data['secure_webengage_host'] = 'https://'.$webengage_host_name;
		$data['resend_email_url'] = '//'.$webengage_host_name.
			'/thirdparty/signup.html?action=resendVerificationEmail&licenseCode='
			.urlencode($m_webengage_license_code).'&next='.urlencode($next_url).
			'&activation_url='.urlencode($activation_url).'&channel=opencart';

		$data['module_route'] = 'module/webengage';
		$data['we_page'] = 'main';
		$data['session_token'] = $this->session->data['token'];

		$this->renderPage($this->language->get('heading_title_main'), 'module/webengage_main.tpl',$data);
	}

	private function handleCallbackPage()
	{
		error_log('your request is coming to handle pageSSS');
		//error_log($_REQUEST[]);
		$data['main_url'] = $this->getUrl('page=main');
		$data['wlc'] = urldecode(isset($_REQUEST['webengage_license_code']) ? htmlspecialchars($_REQUEST['webengage_license_code'], ENT_COMPAT, 'UTF-8') : '');
		$data['vm'] = urldecode(isset($_REQUEST['verification_message']) ? htmlspecialchars($_REQUEST['verification_message'], ENT_COMPAT, 'UTF-8') : '');
		$data['wwa'] = urldecode(isset($_REQUEST['webengage_widget_status']) ? htmlspecialchars($_REQUEST['webengage_widget_status'], ENT_COMPAT, 'UTF-8') : '');
		$data['option'] = urldecode(isset($_REQUEST['option']) ? $_REQUEST['option'] : null);
		error_log($data['vm']);
		error_log('fajfksaf33');
error_log('sucker');
 ob_start();                    // start buffer capture
    var_dump( $data);           // dump the values
    $contents = ob_get_contents(); // put the buffer into a variable
    ob_end_clean();                // end capture
    error_log( $contents ); 
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		}
		else {
			$data['error_warning'] = '';
		}


		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home') ,
			'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL') ,
			'separator' => false
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module') ,
			'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL') ,
			'separator' => ' :: '
		);
		$data['breadcrumbs'][] = array(
			'text' => 'Yo bitch' ,
			'href' => $this->url->link('module/webengage', 'token=' . $this->session->data['token'], 'SSL') ,
			'separator' => ' :: '
		);
		$data['modules'] = array();
		$this->load->model('design/layout');
		$data['layouts'] = $this->model_design_layout->getLayouts();
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		//$this->template = 'module/'.$templateFile;
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');


		//$this->response->setOutput($this->load->view('module/webengage_callback.tpl', $data));
		//$this->response->setOutput($this->render());	

		$this->renderPage('','module/webengage_callback.tpl',$data);
	}

	private function webengageProcessWebengageOptions()
	{
		$redirect_url = "";

		if (isset($_REQUEST['weAction']))
		{
			if ($_REQUEST['weAction'] == 'wp-save')
			{
				$message = $this->webengageUpdateWebengageOptions();
				$redirect_url = $this->getUrl('page=main&'.$message[0].'='.urlencode($message[1]));
			}
			elseif ($_REQUEST['weAction'] == 'reset')
			{
				$message = $this->webengageResetWebengageOptions();
				$redirect_url = $this->getUrl('page=main&'.$message[0].'='.urlencode($message[1]));
			}
			elseif ($_REQUEST['weAction'] == 'activate')
			{
				$message = $this->webengageActivateWeWidget();
				$redirect_url = $this->getUrl('page=main&'.$message[0].'='.urlencode($message[1]));
			}
			elseif ($_REQUEST['weAction'] == 'discardMessage')
			{
				$this->webengageDiscardStatusMessage();
				$redirect_url = $this->getUrl('page=main');
			}

			if (strlen($redirect_url) > 0) {
				$this->response->redirect($redirect_url);
			}
		}
	}

	/**
	* Discard message processor.
	*/
	private function webengageDiscardStatusMessage()
	{
		$webengage_settings = $this->model_setting_setting->getSetting('webengage');

		$data['webengage_license_code'] = isset($webengage_settings['webengage_license_code']) ? $webengage_settings['webengage_license_code'] : '';
		$data['webengage_status'] = '';
		$data['webengage_module'] = $webengage_settings['webengage_module'];
		$this->model_setting_setting->editSetting('webengage', $data);
	}

	/**
	* Resetting processor.
	*/
	private function webengageResetWebengageOptions()
	{
		$webengage_settings = $this->model_setting_setting->getSetting('webengage');

		$data['webengage_license_code'] = '';
		$data['webengage_status'] = '';
		$data['webengage_module'] = $webengage_settings['webengage_module'];
		$this->model_setting_setting->editSetting('webengage', $data);
		
		return array('message', 'Your WebEngage options are deleted. You can signup for a new account.');
	}

	/**
	* Update processor.
	*/
	private function webengageUpdateWebengageOptions()
	{
		$wlc = isset($_REQUEST['webengage_license_code']) ? htmlspecialchars($_REQUEST['webengage_license_code'], ENT_COMPAT, 'UTF-8') : '';
		$vm = isset($_REQUEST['verification_message']) ? htmlspecialchars($_REQUEST['verification_message'], ENT_COMPAT, 'UTF-8') : '';
		$wws = isset($_REQUEST['webengage_widget_status']) ? htmlspecialchars($_REQUEST['webengage_widget_status'], ENT_COMPAT, 'UTF-8') : 'ACTIVE';
        error_log('message coming');
        error_log($wlc);
		if (1==1)
		{
			$webengage_settings = $this->model_setting_setting->getSetting('webengage');

			$data['webengage_license_code'] = trim($wlc);
			
			$data['webengage_status'] = $wws;
			$data['webengage_module'] = $webengage_settings['webengage_module'];
			$this->model_setting_setting->editSetting('webengage', $data);


			$webengage_settings = $this->model_setting_setting->getSetting('webengage');
			error_log('hhhhh2');
error_log('sucker');
 ob_start();                    // start buffer capture
    var_dump( $data);           // dump the values
    $contents = ob_get_contents(); // put the buffer into a variable
    ob_end_clean();                // end capture
    error_log( $contents ); 
			
			$msg = !empty($vm) ? $vm : 'Your WebEngage widget license code has been updated.';
			return array('message', $msg);
		}
		else return array('error-message', 'Please add a license code.');
	}

	/**
	* Activate processor.
	*/
	private function webengageActivateWeWidget()
	{
		$webengage_settings = $this->model_setting_setting->getSetting('webengage');
		$wlc = isset($_REQUEST['webengage_license_code']) ? htmlspecialchars($_REQUEST['webengage_license_code'], ENT_COMPAT, 'UTF-8') : '';
		$old_value = isset($webengage_settings['webengage_license_code']) ? $webengage_settings['webengage_license_code'] : '';
		$wws = isset($_REQUEST['webengage_widget_status']) ? htmlspecialchars($_REQUEST['webengage_widget_status'], ENT_COMPAT, 'UTF-8') : 'ACTIVE';
		if ($wlc === $old_value)
		{
			$webengage_settings = $this->model_setting_setting->getSetting('webengage');

			$data['webengage_license_code'] = $wlc;
			$data['webengage_status'] = $wws;
			$data['webengage_module'] = $webengage_settings['webengage_module'];
			$this->model_setting_setting->editSetting('webengage', $data);

			$msg = 'Your plugin installation is complete. You can do further customizations from your WebEngage dashboard.';
			return array('message', $msg);
		}
		else
		{
			$msg = 'Unauthorized plugin activation request';
			return array('error-message', $msg);
		}
	}

	private function renderPage($headingTitle, $templateFile,$data) {
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		}
		else {
			$data['error_warning'] = '';
		}


		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home') ,
			'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL') ,
			'separator' => false
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module') ,
			'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL') ,
			'separator' => ' :: '
		);
		$data['breadcrumbs'][] = array(
			'text' => $headingTitle ,
			'href' => $this->url->link('module/webengage', 'token=' . $this->session->data['token'], 'SSL') ,
			'separator' => ' :: '
		);
		$data['modules'] = array();
		$this->load->model('design/layout');
		$data['layouts'] = $this->model_design_layout->getLayouts();
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		$this->template = 'module/'.$templateFile;
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($templateFile, $data));
		//$this->response->setOutput($this->render());	
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/webengage')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
} 
?>

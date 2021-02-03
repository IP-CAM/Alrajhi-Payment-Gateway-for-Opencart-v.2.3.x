<?php
class ControllerExtensionPaymentArb extends Controller
{
	private $error = array();

	public function index()
	{
		$this->load->language('extension/payment/arb');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('arb', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_extension'] = $this->language->get('text_extension');
		$data['text_success'] = $this->language->get('text_success');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['entry_trans_id'] = $this->language->get('entry_trans_id');
		$data['entry_security'] = $this->language->get('entry_security');
		$data['entry_resource']	= $this->language->get('entry_resource');
		$data['entry_endpoint']	= $this->language->get('entry_endpoint');
		$data['entry_callback']	= $this->language->get('entry_callback');
		$data['entry_total']	=	$this->language->get('entry_total');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_geo_zone']	= $this->language->get('entry_geo_zone');
		$data['entry_status']	=	$this->language->get('entry_status');
		$data['entry_sort_order'] =	$this->language->get('entry_sort_order');
		$data['help_callback']	=	$this->language->get('help_callback');
		$data['help_total']		= $this->language->get('help_total');
		$data['error_permission'] =	$this->language->get('error_permission');
		$data['error_trans_id']	= $this->language->get('error_trans_id');
		$data['error_security']	= $this->language->get('error_security');
		$data['error_resource']	= $this->language->get('error_resource');
		$data['error_endpoint']	= $this->language->get('error_endpoint');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');


		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['trans_id'])) {
			$data['error_trans_id'] = $this->error['trans_id'];
		} else {
			$data['error_trans_id'] = '';
		}

		if (isset($this->error['security'])) {
			$data['error_security'] = $this->error['security'];
		} else {
			$data['error_security'] = '';
		}
		if (isset($this->error['resource'])) {
			$data['error_resource'] = $this->error['resource'];
		} else {
			$data['error_resource'] = '';
		}
		if (isset($this->error['endpoint'])) {
			$data['error_endpoint'] = $this->error['endpoint'];
		} else {
			$data['error_endpoint'] = '';
		}

		$data['breadcrumbs'] = array();
	


			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_extension'),
				'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/payment/arb', 'token=' . $this->session->data['token'], true)
			);

			$data['action'] = $this->url->link('extension/payment/arb', 'token=' . $this->session->data['token'], true);

			$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);
		
		if (isset($this->request->post['arb_trans_id'])) {
			$data['arb_trans_id'] = $this->request->post['arb_trans_id'];
		} else {
			$data['arb_trans_id'] = $this->config->get('arb_trans_id');
		}

		if (isset($this->request->post['arb_security'])) {
			$data['arb_security'] = $this->request->post['arb_security'];
		} else {
			$data['arb_security'] = $this->config->get('arb_security');
		}
		if (isset($this->request->post['arb_resource'])) {
			$data['arb_resource'] = $this->request->post['arb_resource'];
		} else {
			$data['arb_resource'] = $this->config->get('arb_resource');
		}
		if (isset($this->request->post['arb_endpointe'])) {
			$data['arb_endpoint'] = $this->request->post['arb_endpoint'];
		} else {
			$data['arb_endpoint'] = $this->config->get('arb_endpoint');
		}

		$data['callback'] = HTTP_CATALOG . 'index.php?route=extension/payment/arb/callback';

		if (isset($this->request->post['arb_total'])) {
			$data['arb_total'] = $this->request->post['arb_total'];
		} else {
			$data['arb_total'] = $this->config->get('arb_total');
		}

		if (isset($this->request->post['arb_order_status_id'])) {
			$data['arb_order_status_id'] = $this->request->post['arb_order_status_id'];
		} else {
			$data['arb_order_status_id'] = $this->config->get('arb_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['arb_geo_zone_id'])) {
			$data['arb_geo_zone_id'] = $this->request->post['arb_geo_zone_id'];
		} else {
			$data['arb_geo_zone_id'] = $this->config->get('arb_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['arb_status'])) {
			$data['arb_status'] = $this->request->post['arb_status'];
		} else {
			$data['arb_status'] = $this->config->get('arb_status');
		}

		if (isset($this->request->post['arb_sort_order'])) {
			$data['arb_sort_order'] = $this->request->post['arb_sort_order'];
		} else {
			$data['arb_sort_order'] = $this->config->get('arb_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/arb', $data));
	}

	protected function validate()
	{
		if (!$this->user->hasPermission('modify', 'extension/payment/arb')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['arb_trans_id']) {
			$this->error['trans_id'] = $this->language->get('error_trans_id');
		}

		if (!$this->request->post['arb_security']) {
			$this->error['security'] = $this->language->get('error_security');
		}
		if (!$this->request->post['arb_resource']) {
			$this->error['resource'] = $this->language->get('error_resource');
		}
		if (!$this->request->post['arb_endpoint']) {
			$this->error['endpoint'] = $this->language->get('error_endpoint');
		}

		return !$this->error;
	}
}

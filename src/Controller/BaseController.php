<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Utils\Constants;
use App\Utils\Pagination;
use App\Utils\Response;
use App\Utils\Codes;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;
use JMS\Serializer\SerializationContext;

class BaseController extends AbstractController {

	private $tamanioPagina = 10;
	private $serializer = null;
	protected $response = null;
	protected $data = [];
	protected $pagination;
	protected $session;

	function __construct() {
		$this->response = new Response();
		$this->response->setData(null);
		$this->response->setCode(HttpResponse::HTTP_OK);
		$this->response->setMessage("");
		$this->data['title'] = '';
	}

	/**
	 * @return int
	 */
	public function getTamanioPagina() {
		return $this->tamanioPagina;
	}

	/**
	 * @param int $tamanioPagina
	 */
	public function setTamanioPagina(int $tamanioPagina) {
		$this->tamanioPagina = $tamanioPagina;
	}

	public function toJson($object) {
		$this->serializer = SerializerBuilder::create()->build();
		return $this->serializer->serialize($object, 'json');
	}

	public function setTitle(string $title) {
		$this->data['title'] = $title;
	}

	public function isUserLogged() {
		$user = $this->getUser();
		return $user instanceof \App\Entity\Usuario;
	}

	/**
	 * Devuelve el usuario loggeado, solo los datos necesarios
	 * @return type
	 */
	public function getUserLoggedData() {
		$user = $this->getUser();
		$serializer = SerializerBuilder::create()->build();
		$serializerContext = SerializationContext::create();
		$serializerContext->setGroups(array("userdata"));
		$serializerContext->setSerializeNull(true);
		return $serializer->toArray($user, $serializerContext);
	}

	/**
	 * Retorna verdadero si el rol de la persona logueada es admin
	 * @param type $roles
	 * @return boolean
	 */
	public function isRolAdmin($roles) {
		$isAdmin = false;
		foreach ($roles as $r) {
			if ($r === Constants::ROLE_ADMIN) {
				$isAdmin = true;
			}
		}
		return $isAdmin;
	}

	/**
	 * Retorna verdadero si el rol de la persona logueada es operador
	 * @param type $roles
	 * @return boolean
	 */
	public function isRolOperador($roles) {
		$isOperador = false;
		foreach ($roles as $r) {
			if ($r === Constants::ROLE_OPERADOR) {
				$isOperador = true;
			}
		}
		return $isOperador;
	}

	/**
	 * Retorna verdadero si el rol de la persona logueada es administracion
	 * @param type $roles
	 * @return boolean
	 */
	public function isRolAdministracion($roles) {
		$isAdministracion = false;
		foreach ($roles as $r) {
			if ($r === Constants::ROLE_ADMINISTRACION) {
				$isAdministracion = true;
			}
		}
		return $isAdministracion;
	}

	public function isRolUser($roles) {
		$isUser = false;
		foreach ($roles as $r) {
			if ($r === Constants::ROLE_USER) {
				$isUser = true;
			}
		}
		return $isUser;
	}

	public function setBreadCrumb(array $breadcrumbs) {
		$this->data['breadcrumbs'] = $breadcrumbs;
	}

	function getPagination() {
		return new Pagination();
	}

	/**
	 * Agrega breadcrumbs a las vistas
	 * @param string $label label a mostrarse en el breadcrubs
	 * @param bool $active si estara activo, en caso de false corresponde enlace
	 * @param string $route ruta del enlace en caso de que no este activo
	 * @param array $params si la ruta requiere parametros, se lo debe pasar en esete parametro
	 */
	public function addBreadCrumb(string $label, bool $active = false, string $route = "", array $params = array()) {
		if (!isset($this->data['breadcrumbs']) || $this->data['breadcrumbs'] == null || !is_array($this->data['breadcrumbs'])) {
			$this->data['breadcrumbs'] = array();
		}
		array_push($this->data['breadcrumbs'], array("label" => $label,
			"active" => $active,
			"route" => $route,
			"params" => $params
		));
	}

	protected function getHomeRoute(): string
	{
		if ($this->isGranted('ROLE_ADMIN')) {
			return 'admin_home';
		}

		if ($this->isGranted('ROLE_ADMINISTRACION')) {
			return 'administracion_home';
		}

		if ($this->isGranted('ROLE_OPERADOR')) {
			return 'operador_home';
		}

		return 'app_login';
	}

}
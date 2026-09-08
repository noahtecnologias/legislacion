<?php

namespace App\Service;

use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;
use App\Utils\Constants;

class BaseService {

    
	protected $userContext = null;
	protected $context = null;
	protected $tokenStorage = null;
	protected $router;

	function __construct(TokenStorageInterface $tokenStorage, RouterInterface $router, ContainerInterface $container, EntityManagerInterface $entityManager) {
                                $this->om = $entityManager;
                                $this->container = $container;
		$this->tokenStorage = $tokenStorage;
		$this->router = $router;
	}

	public function toarray($object, $group = null) {
		$this->serializer = SerializerBuilder::create()->build();
		$serializerContext = SerializationContext::create();
		if (!is_null($group)) {
			$serializerContext->setGroups(array($group));
		}
		$serializerContext->setSerializeNull(true);

		return $this->serializer->toArray($object, $serializerContext);
	}

	public function isRolAdmin() {
		$this->userContext = $this->tokenStorage->getToken()->getUser();
		$roles = $this->userContext->getRol();
		$isAdmin = false;
		foreach ($roles as $r) {
			if ($r->getNombre() === Constants::ROLE_ADMIN) {
				$isAdmin = true;
			}
		}
		return $isAdmin;
	}

	public function isRolSecretaria() {
		$this->userContext = $this->tokenStorage->getToken()->getUser();
		$roles = $this->userContext->getRol();
		$isSecretaria = false;
		foreach ($roles as $r) {
			if ($r->getNombre() === Constants::ROLE_SECRETARIA) {
				$isSecretaria = true;
			}
		}
		return $isSecretaria;
	}

	public function isRolMedico() {
		$this->userContext = $this->tokenStorage->getToken()->getUser();
		$roles = $this->userContext->getRol();
		$isMedico = false;
		foreach ($roles as $r) {
			if ($r->getNombre() === Constants::ROLE_MEDICO) {
				$isMedico = true;
			}
		}
		return $isMedico;
	}

	public function isRolPaciente() {
		$this->userContext = $this->tokenStorage->getToken()->getUser();
		$roles = $this->userContext->getRol();
		$isPaciente = false;
		foreach ($roles as $r) {
			if ($r->getNombre() === Constants::ROLE_PACIENTE) {
				$isPaciente = true;
			}
		}
		return $isPaciente;
	}

}
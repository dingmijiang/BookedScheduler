<?php 
require_once(ROOT_DIR . 'lib/Config/namespace.php');
require_once(ROOT_DIR . 'lib/Server/namespace.php');
require_once(ROOT_DIR . 'lib/Common/namespace.php');
require_once(ROOT_DIR . 'Domain/namespace.php');
require_once(ROOT_DIR . 'Domain/Access/namespace.php');

abstract class ReservationPresenterBase implements IReservationPresenter
{
	/**
	 * @var IReservationPage
	 */
	protected $basePage;
	
	protected function __construct(IReservationPage $page)
	{
		$this->basePage = $page;
	}
	
	public abstract function PageLoad();
}

class ReservationPresenter extends ReservationPresenterBase
{
	/**
	 * @var INewReservationPage
	 */
	private $_page;
	
	/**
	 * @var IReservationInitializerFactory
	 */
	private $initilizationFactory;
	
	/**
	 * @var IReservationPreconditionService
	 */
	private $precondtionService;

	public function __construct(
		INewReservationPage $page, 
		IReservationInitializerFactory $initializationFactory,
		INewReservationPreconditionService $precondtionService)
	{
		parent::__construct($page);
		
		$this->_page = $page;
		$this->initializationFactory = $initializationFactory;
		$this->preconditionService = $precondtionService;
	}
	
	public function PageLoad()
	{
		$user = ServiceLocator::GetServer()->GetUserSession();

		$this->preconditionService->CheckAll($this->_page, $user);
		$initializer = $this->initializationFactory->GetNewInitializer($this->_page);
		$initializer->Initialize();
	}
}

class EditReservationPresenter extends ReservationPresenterBase
{
	/**
	 * @var IExistingReservationPage
	 */
	private $page;
	
	/**
	 * @var IReservationInitializerFactory
	 */
	private $initializationFactory;
	
	/**
	 * @var EditReservationPreconditionService
	 */
	private $preconditionService;
	
	/**
	 * @var IReservationViewRepository
	 */
	private $reservationViewRepository;

	public function __construct(
		IExistingReservationPage $page, 
		IReservationInitializerFactory $initializationFactory,
		EditReservationPreconditionService $precondtionService,
		IReservationViewRepository $reservationViewRepository)
	{
		parent::__construct($page);
		
		$this->page = $page;
		$this->initializationFactory = $initializationFactory;
		$this->preconditionService = $precondtionService;
		$this->reservationViewRepository = $reservationViewRepository;
	}
	
	public function PageLoad()
	{
		$user = ServiceLocator::GetServer()->GetUserSession();

		$referenceNumber = $this->page->GetReferenceNumber();
		$reservationView = $this->reservationViewRepository->GetReservationForEditing($referenceNumber);
		
		$this->preconditionService->CheckAll($this->page, $user, $reservationView);
		$initializer = $this->initializationFactory->GetExisitingInitializer($this->page, $reservationView);
		$initializer->Initialize();
	}
}

interface IReservationPresenter
{}


?>
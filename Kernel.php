<?php

namespace Sirius;

use Sirius\controller\ControllerException;
use Sirius\database\DatabaseResolver;
use Sirius\database\EntityManager;
use Sirius\http\Request;
use Sirius\http\Response;
use Sirius\Event\Dispatcher;
use Sirius\Event\EventNames;
use Sirius\Event\ListenerService;
use Sirius\Event\Events\RequestEvent;
use Sirius\Event\Events\ControllerEvent;
use Sirius\controller\ControllerResolver;
use Sirius\controller\ArgumentsResolver;
use Sirius\routing\Router;
use Sirius\utils\JsonParser;
use Exception;
use RuntimeException;
use Throwable;


/**
 * Class Kernel
 * @package Sirius
 */
class Kernel
{
    /**
     * @var ?Dispatcher
     */
    protected ?Dispatcher $dispatcher = null;

    /**
     * @var ListenerService
     */
    protected ListenerService $listenerService;

    /**
     * @var ControllerResolver
     */
    protected ControllerResolver $controllerResolver;

    public ?EntityManager $entityManager = null;

    /**
     * @var ArgumentsResolver
     */
    protected ArgumentsResolver $argumentResolver;

    private ?Request $request = null;

    private ?string $rootPath = null;

    protected ?Container $container = null;

    public function setServices()
    {
        try {
            if (null === $this->dispatcher) {
                $this->setDispatcher();
            }

            $dispatcher = $this->dispatcher;
            $this->listenerService = new ListenerService($dispatcher);
            $this->listenerService->setListeners();
            $this->argumentResolver = new ArgumentsResolver();
            $this->entityManager = $this->container->getEntityManager();
            $this->controllerResolver = new ControllerResolver();
        } catch (Exception $e) {
            $this->throwResponse($e);
        }

    }

    public function setRootPath(): string
    {
        if (null === $this->rootPath) {
            if (defined('ROOT_DIR')) {
                $this->rootPath = ROOT_DIR;
                return  $this->rootPath;
            }

            $r = new \ReflectionObject($this);
            if (!is_file($dir = $r->getFileName())) {
                throw new \LogicException(sprintf('Cannot auto-detect project dir for kernel of class "%s".', $r->name));
            }

            $dir =  \dirname(\dirname($dir));
            while (!is_file($dir.'/composer.json')) {
                if ($dir === \dirname($dir)) {
                    break;
                }
                $dir = \dirname($dir);
            }

            if (!is_file($dir.'/composer.json')) {
                throw new \LogicException(sprintf('Cannot auto-detect project dir for kernel of class "%s".', $r->name));
            }

            $this->rootPath = $dir;
            define('ROOT_DIR', $dir);
        }

        return $this->rootPath;
    }

    public function setDispatcher()
    {
        $this->dispatcher = new Dispatcher();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function handleRequest(Request $request):Response
    {
        $this->container ?? $this->bootApp();
        $this->request = $request;
        try {
            return $this->route($request);
        } catch (Exception $e) {
            $this->throwResponse($e);
        }
    }
    /**
     * @param Request $request
     * @return Response
     */
    public function route(Request $request):Response
    {
        $event = new RequestEvent($this, $request);
        $this->dispatcher->dispatch($event, EventNames::REQUEST);

        $controller = $this->controllerResolver->getController($request, $this->entityManager);

        $event = new ControllerEvent($this, $controller);
        $this->dispatcher->dispatch($event, EventNames::CONTROLLER);

        $arguments = $this->argumentResolver->getArguments($request, $controller);

        $response = $controller(...$arguments);

        if (!$response instanceof Response) {
            throw new ControllerException('No response sent back from controller', 500);
        }

        return $response;
    }

    public function throwResponse(Throwable $e)
    {
        $controller = Router::matchError();
        if (!$this->request) {
            $this->request = Request::create();
        }

        $options['entityManager'] = false;
        $controller = ControllerResolver::createController($controller, $this->request, $this->entityManager);
        $controllerResponse = $controller($e);
        $e->getCode() === 404 ? $controllerResponse->setStatusCode(404):$controllerResponse->setStatusCode(500);
        $controllerResponse->send();
        exit();
    }

    public function bootApp(): Container
    {
        $container = $this->container = Container::getInstance();
        $this->setServices();
        return $container;
    }

    public function getContainer(): ?Container
    {
        if (null === $this->container) {
            throw new \LogicException('The container is not initialized, the kernel needs to be booted first');
        }

        return $this->container;
    }
}

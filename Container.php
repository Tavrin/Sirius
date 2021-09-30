<?php


namespace Sirius;


use Sirius\database\DatabaseResolver;
use Sirius\database\EntityManager;
use Sirius\utils\JsonParser;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

class Container
{
    private static ?Container $instance = null;
    private ?array $services = null;


    private ?Environment $twig = null;
    private ?EntityManager $entityManager = null;

    private ?string $rootPath = null;

    private function __construct()
    {
        if (file_exists($this->rootPath.'/config/services.json')) {
            $this->services = JsonParser::parseFile($this->rootPath . '/config/services.json');
        }
        $this->setTwig();
    }

    public static function getInstance():Container
    {
        if (self::$instance == null)
        {
            self::$instance = new Container();
        }

        return self::$instance;
    }

    /**
     * @throws \Exception
     */
    public function getEntityManager(): ?EntityManager
    {
        if (!$this->entityManager) {
            try {
                $this->entityManager = DatabaseResolver::instantiateManager();
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return $this->entityManager;
    }

    /**
     * @return string
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    /**
     * @return Environment|null
     */
    public function getTwig(): ?Environment
    {
        return $this->twig;
    }

    private function setTwig()
    {
        if (is_dir($this->rootPath.'/templates/')) {
            $loader = new FilesystemLoader($this->rootPath . '/templates/');

            if (isset($_ENV['ENV']) && $_ENV['ENV'] === 'dev') {
                $this->twig = new Environment($loader, [
                    'debug' => true
                ]);
                $this->twig->addExtension(new DebugExtension());
            } else {
                $this->twig = new Environment($loader, [
                    'debug' => false
                ]);
            }
        }

        $this->setTwigServices();
    }

    /**
     * @return string
     */
    private function setRootPath(): string
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

    private function setTwigServices()
    {
        if (isset ($this->services['twig']['extensions'])) {
            foreach ($this->services['twig']['extensions'] as $extension) {
                $class = $extension['class'];
                $this->twig->addExtension(new $class());
            }
        }
    }
}
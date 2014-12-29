<?php

namespace DevNanny\GitHook;

use DevNanny\GitHook\Interfaces\RepositoryContainerInterface;

class Installer
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    const PRE_COMMIT = 'pre-commit';

    const ERROR_UNSUPPORTED_HOOK = 'Hook of type "%s" is not supported, must be one of "%s"';
    const ERROR_HOOK_ALREADY_EXISTS = 'Other hook "%s" already exists';

    /** @var \SplFileObject[] */
    private $files;
    /** @var \Gitonomy\Git\Hooks */
    private $hooks;
    /** @var array */
    private $supported = array(
        self::PRE_COMMIT,
    );

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    final public function __construct(RepositoryContainerInterface $container)
    {
        $this->hooks = $container->getHooks();
    }

    final public function install($name)
    {
        $hooks = $this->hooks;
        $present = $hooks->has($name);

        if ($this->isSupported($name) === false) {
            $message = sprintf(
                self::ERROR_UNSUPPORTED_HOOK,
                $name,
                implode('", "', $this->supported)
            );
            throw new \UnexpectedValueException($message);
        } elseif ($this->validateHook($name) === true) {
            $installed = true;
        } elseif ($present === true) {
            throw new \UnexpectedValueException(sprintf(self::ERROR_HOOK_ALREADY_EXISTS, $name));
        } else {
            $path = $this->getSourcePath($name);
            $hooks->setSymlink($name, $path);
            $installed = true;
        }

        return $installed;
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param string $name
     *
     * @return string
     */
    private function getSourcePath($name)
    {
        return sprintf('%s/git/hook/%s', realpath(__DIR__ . '/..'), $name);
    }

    /**
     * Validate that a given hook exists and that its contents is equal to the
     * content of the source file for the given hook. This works regardless if
     * the hook has been installed as a symlink or not.
     *
     * @param string $name
     *
     * @return bool
     */
    private function validateHook($name)
    {
        $valid = false;

        if ($this->hooks->has($name)) {
            $valid = ($this->getSourceContent($name) === $this->hooks->get($name));
        }

        return $valid;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function getSourceContent($name)
    {
        $content = '';

        $file = $this->getSourceFile($name);

        // @NOTE: SplFileObject::fread is available since 5.5.11
        foreach ($file as $line) {
            $content .= $line;
        }

        return $content;
    }

    /**
     * @param string $name
     *
     * @return \SplFileObject
     */
    private function getSourceFile($name)
    {
        $path = $this->getSourcePath($name);

        if (isset($this->files[$name]) === false) {
            $this->files[$name] = new \SplFileObject($path);
        }

        return $this->files[$name];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function isSupported($name)
    {
        return in_array($name, $this->supported);
    }
}

/*EOF*/

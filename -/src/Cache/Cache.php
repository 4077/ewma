<?php namespace ewma\Cache;

use ewma\App\App;
use ewma\Service\Service;

class Cache extends Service
{
    protected $services = ['app'];

    /**
     * @var App
     */
    public $app = App::class;

    const PHP = 0;

    const JSON = 1;

    private $dir;

    private $mode;

    public function setDir($dir)
    {
        $this->dir = $dir;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    public function read($filePath)
    {
        $filePath = abs_path($this->dir, $filePath);

        if ($this->mode == self::PHP) {
            return aread($filePath . '.php');
        }

        if ($this->mode == self::JSON) {
            return jread($filePath . '.json');
        }
    }

    public function write($filePath, $input)
    {
        $filePath = abs_path($this->dir, $filePath);

        if ($this->mode == self::PHP) {
            awrite($filePath . '.php', $input);
        }

        if ($this->mode == self::JSON) {
            jwrite($filePath . '.json', $input);
        }
    }

    public function reset($filePath)
    {
        $absFilePath = abs_path($this->dir, $filePath);

        if (is_dir($absFilePath)) {
            exec('rm -fr ' . $absFilePath);

            $reseted = true;
        } else {
            if ($this->mode == self::PHP) {
                $absFilePath .= '.php';
            }

            if ($this->mode == self::JSON) {
                $absFilePath .= '.json';
            }

            if (is_file($absFilePath)) {
                exec('rm ' . $absFilePath);
                $reseted = true;

            }
        }

        if (!empty($reseted)) {
//            $this->app->events->trigger('app/cache/reset/' . $filePath);

            return true;
        }
    }
}

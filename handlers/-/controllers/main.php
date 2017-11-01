<?php namespace ewma\handlers\controllers;

class Main extends \Controller
{
    private $namedOutputs = [];

    public function compileAllOutputs()
    {
        $outputs = \ewma\handlers\models\Assignment::where('type', 'OUTPUT')->get();

        $this->namedOutputs = aread(abs_path('cache/handlers/namedOutputs.php')); // todo app->cachePath

        foreach ($outputs as $output) {
            $this->data('output_id', $output->id);

            $this->compileOutput();
        }

        awrite(abs_path('cache/handlers/namedOutputs.php'), $this->namedOutputs); // todo app->cachePath
    }

    public function compileOutput()
    {
        $outputId = $this->data['output_id'];

        $output = $this->c('compiler')->compileOutput($outputId);

        if ($output['name']) {
            $this->namedOutputs[$output['name']] = $outputId;
        }

        awrite(abs_path('cache/handlers/outputs/' . $outputId . '.php'), $output); // todo app->cachePath
    }

    public function renderOutput($outputIdOrName, $vars = [])
    {
        if (!$vars) {
            $vars = $this->data;
        }

        if (is_numeric($outputIdOrName)) {
            $outputId = $outputIdOrName;
        } else {
            $namedOutputs = aread(abs_path('cache/handlers/namedOutputs.php')); // todo app->cachePath

            $outputId = $namedOutputs[$outputIdOrName];
        }

        $output = aread(abs_path('cache/handlers/outputs/' . $outputId . '.php')); // todo app->cachePath

        return $this->c('renderer')->renderOutput($output, $vars);
    }
}

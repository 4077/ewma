<?php namespace ewma\handlers\controllers;

use ewma\Views\View;

class Renderer extends \Controller
{
    private $vars = [];

    public function renderOutput($output, $vars = [])
    {
        if ($vars) {
            ra($this->vars, $vars);
        }

        return $this->renderContainer($output);
    }

    private function renderContainer($container) // output, var, input
    {
        $combineMode = $container['combine_mode'];
        $assignments = $container['assignments'];

        $output = null;

        foreach ($assignments as $assignment) {
            if ($combineMode == 'link') {
                $renderedAssignment = &$this->renderAssignment($assignment);
            } else {
                $renderedAssignment = $this->renderAssignment($assignment);
            }

            if (null === $renderedAssignment) {
                if (!empty($container['required'])) {
                    return null;
                }
            } else {
                if ($combineMode == 'first' || $combineMode == 'link') {
                    return $renderedAssignment;
                }

                if ($combineMode == 'concat' && is_scalar($renderedAssignment)) {
                    $output .= $renderedAssignment;
                }

                if ($combineMode == 'array') {
                    $output[] = $renderedAssignment;
                }

                if ($combineMode == 'add' && (is_scalar($renderedAssignment) || is_array($renderedAssignment))) {
                    if (!isset($addCombineModeType)) {
                        if (is_scalar($renderedAssignment)) {
                            $addCombineModeType = 'scalar';
                        } else { // is_array
                            $addCombineModeType = 'array';
                        }
                    }

                    if (is_scalar($renderedAssignment) && $addCombineModeType == 'scalar') {
                        if (is_null($output)) {
                            $output = '';
                        }

                        $output += $renderedAssignment;
                    }

                    if (is_array($renderedAssignment) && $addCombineModeType == 'array') {
                        if (is_null($output)) {
                            $output = [];
                        }

                        $output += $renderedAssignment;
                    }
                }

                if ($combineMode == 'aa' && is_array($renderedAssignment)) {
                    if (is_null($output)) {
                        $output = [];
                    }

                    aa($output, $renderedAssignment);
                }

                if ($combineMode == 'ra' && is_array($renderedAssignment)) {
                    if (is_null($output)) {
                        $output = [];
                    }

                    ra($output, $renderedAssignment);
                }
            }
        }

        return $output;
    }

    private function renderAssignment($assignment)
    {
        $output = [];

        if ($assignment['type'] == 'HANDLER') {
            $renderedHandler = $this->renderHandler($assignment['path'], $assignment['assignments']);

            if ($assignment['required'] && null === $renderedHandler) {
                return null;
            } else {
                return $output[] = $renderedHandler;
            }
        }

        if ($assignment['type'] == 'VALUE') {
            return $this->renderValue($assignment['value']);
        }
    }

    private function renderHandler($path, $assignments)
    {
        $data = [];

        $varsBackup = $this->vars;

        foreach ($assignments as $assignment) {
            $renderedContainer = $this->renderContainer($assignment);

            if ($assignment['required'] && null === $renderedContainer) {
                return null;
            }

            if ($assignment['type'] == 'VAR') {
                $varsNodePath = str_replace(':', '/', $assignment['path']);
                $node = &ap($this->vars, $varsNodePath);
            }

            if ($assignment['type'] == 'INPUT') {
                $node = &ap($data, $assignment['path']);
            }

            $node = $renderedContainer;
        }

        $this->vars = $varsBackup;

        if (false !== strpos($path, '{')) {
            $path = \ewma\Data\Data::tokenize($path, $this->vars);
        }

        $output = $this->app->c($path, $data);

        if ($output instanceof View) {
            return $output->render();
        } else {
            return $output;
        }
    }

    private function renderValue($value) // todo||not todo замены в массивах
    {
        if (is_scalar($value)) {
            // если значение состоит только из переменной, то полностью заменяется на
            // ее значение, если она существует
            if (preg_match('/^\{([\w\/]+)\}$/', trim($value), $varNameMatch)) {
                list($varName, $path) = array_pad(explode(':', $varNameMatch[1]), 2, null);

                if (null !== $outputValue = ap($this->vars, $varName)) { // можно оптимизировать, если нет слэша то не дергать ap()
//                    $outputValue = $this->vars[$varName];

                    if (null !== $path) {
                        $outputValue = ap($outputValue, $path);
                    }
                } else {
                    $outputValue = null;
                }
            } else {
                $outputValue = $value;

                foreach ($this->vars as $varName => $varValue) { // todo сделать так же по массивам как в предыдущем блоке
//                    if (false !== strpos($varName, ':')) {
//                        list($varName, $path) = explode(':', $value);
//
//                        if (is_array($varValue)) {
//
//                        }
//                    }

                    // todo поиск регуляркой

                    if (is_scalar($varValue)) {
                        $outputValue = str_replace('{' . $varName . '}', $varValue, $outputValue);
                    }
                }

                $outputValue = preg_replace('/\{.+\}/', '', $outputValue);
            }

            return $outputValue;
        } else {
            return $value;
        }
    }
}

<?php namespace ewma\ui\css\controllers;

class Main extends \Controller
{
    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $dCss = $this->d('^');

        $compiler = $dCss['compiler'];
        $combiner = $dCss['combiner'];

        $v = $this->v();

        $v->assign([
                       'INCREASE_VERSION_BUTTON'         => $this->c('\std\ui button:view', [
                           'path'    => '>xhr:increaseVersion',
                           'class'   => 'button increase_version',
                           'content' => $dCss['version']
                       ]),
                       //
                       // compiler
                       //
                       'COMPILER_ENABLED_TOGGLE_BUTTON'  => $this->c('\std\ui button:view', [
                           'path'    => '>xhr:toggleCompiler',
                           'class'   => 'button toggle_compiler ' . ($compiler['enabled'] ? 'pressed' : ''),
                           'content' => $compiler['enabled'] ? 'enabled' : 'disabled'
                       ]),
                       'COMPILER_DEV_MODE_TOGGLE_BUTTON' => $this->c('\std\ui button:view', [
                           'visible' => $compiler['enabled'],
                           'path'    => '>xhr:toggleCompilerDevMode',
                           'class'   => 'button toggle_compiler_dev_mode ' . ($compiler['dev_mode'] ? 'pressed' : ''),
                           'content' => 'dev mode'
                       ]),
                       'COMPILER_MINIFY_TOGGLE_BUTTON'   => $this->c('\std\ui button:view', [
                           'visible' => $compiler['enabled'] && !$compiler['dev_mode'],
                           'path'    => '>xhr:toggleCompilerMinify',
                           'class'   => 'button toggle_compiler_minify ' . ($compiler['minify'] ? 'pressed' : ''),
                           'content' => 'minify'
                       ]),
                       'COMPILER_DIR_TXT'                => $this->c('\std\ui txt:view', [
                           'path'              => '>xhr:compilerDirSet',
                           'class'             => 'txt',
                           'fitInputToClosest' => '.cell',
                           'placeholder'       => '',
                           'content'           => $compiler['dir']
                       ]),
                       'COMPILER_DEV_MODE_DIR_TXT'       => $this->c('\std\ui txt:view', [
                           'path'              => '>xhr:compilerDevModeDirSet',
                           'class'             => 'txt',
                           'fitInputToClosest' => '.cell',
                           'placeholder'       => '',
                           'content'           => $compiler['dev_mode_dir']
                       ]),
                       //
                       // combiner
                       //
                       'COMBINER_ENABLED_TOGGLE_BUTTON'  => $this->c('\std\ui button:view', [
                           'clickable' => !$compiler['enabled'],
                           'path'      => '>xhr:toggleCombiner',
                           'class'     => 'button toggle_combiner ' . ($compiler['enabled']
                                   ? 'pressed disabled'
                                   : ($combiner['enabled'] ? 'pressed' : '')),
                           'content'   => $compiler['enabled'] || $combiner['enabled'] ? 'enabled' : 'disabled'
                       ]),
                       'COMBINER_USE_TOGGLE_BUTTON'      => $this->c('\std\ui button:view', [
                           'clickable' => !$compiler['dev_mode'],
                           'path'      => '>xhr:toggleCombinerUse',
                           'class'     => 'button toggle_combiner_use ' . ($combiner['use'] && !$compiler['dev_mode'] ? 'pressed' : ($compiler['dev_mode'] ? 'disabled' : '')),
                           'content'   => 'use'
                       ]),
                       'COMBINER_MINIFY_TOGGLE_BUTTON'   => $this->c('\std\ui button:view', [
                           'path'    => '>xhr:toggleCombinerMinify',
                           'class'   => 'button toggle_combiner_minify ' . ($combiner['minify'] ? 'pressed' : ''),
                           'content' => 'minify'
                       ]),
                       'COMBINER_DIR_TXT'                => $this->c('\std\ui txt:view', [
                           'path'              => '>xhr:combinerDirSet',
                           'class'             => 'txt',
                           'fitInputToClosest' => '.cell',
                           'placeholder'       => '',
                           'content'           => $combiner['dir']
                       ]),
                   ]);

        $this->css(':themes/default', [
            'label'    => [
//                'color' => '#ff0000'
            ],
            'compiler' => [
                'enabled' => [
//                    'backgroundColor' => '#ff50ff',
//                    'color'           => '#440000'
                ]
            ]
        ]);

        return $v;
    }
}
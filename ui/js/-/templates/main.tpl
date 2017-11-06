<div class="{__NODE_ID__}">

    <div class="table">
        <div class="row version">
            <div class="cell label">version</div>
            <div class="cell">{INCREASE_VERSION_BUTTON}</div>
        </div>

        <div class="row compiler">
            <div class="cell label">compiler</div>
            <div class="cell">
                {COMPILER_ENABLED_TOGGLE_BUTTON}
                {COMPILER_DEV_MODE_TOGGLE_BUTTON}
                {COMPILER_MINIFY_TOGGLE_BUTTON}
                <div class="cb"></div>
                <div class="table dirs">
                    <div class="row">
                        <div class="cell left">
                            <div class="label">output dir</div>
                        </div>
                        <div class="cell">{COMPILER_DIR_TXT}</div>
                    </div>
                    <div class="row">
                        <div class="cell left">
                            <div class="label">dev mode output dir</div>
                        </div>
                        <div class="cell">{COMPILER_DEV_MODE_DIR_TXT}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row combiner">
            <div class="cell label">combiner</div>
            <div class="cell">
                {COMBINER_ENABLED_TOGGLE_BUTTON}
                {COMBINER_USE_TOGGLE_BUTTON}
                {COMBINER_MINIFY_TOGGLE_BUTTON}
                <div class="cb"></div>
                <div class="table dirs">
                    <div class="row">
                        <div class="cell left">
                            <div class="label">output dir</div>
                        </div>
                        <div class="cell">{COMBINER_DIR_TXT}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="{__NODE_ID__}" instance="{__INSTANCE__}">

    {COMPILE_BUTTON}

    <div class="table">
        <div class="row">
            <div class="cell label">Название</div>
            <div class="cell value">{NAME_TXT}</div>
        </div>

        <div class="row">
            <div class="cell label">Паттерн</div>
            <div class="cell value">
                <div class="pattern">
                    <div class="base_pattern">{BASE_ROUTE_PATTERN}</div>
                    <div class="route_pattern">{PATTERN_TXT}</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="cell label">Обертка ответа</div>
            <div class="cell value">
                <div class="wrapper_selector">
                    {EWMA_HTML_WRAPPER_BUTTON}
                    {NO_WRAPPER_BUTTON}
                    <div class="cb"></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="cell label">Тип</div>
            <div class="cell value">
                <div class="target_type_selector">
                    {TARGET_TYPE_METHOD_BUTTON}
                    {TARGET_TYPE_HANDLERS_OUTPUT_BUTTON}
                    <div class="cb"></div>
                </div>
            </div>
        </div>

        <!-- method -->
        <div class="row">
            <div class="cell label">Путь</div>
            <div class="cell value">{PATH_TXT}</div>
        </div>

        <div class="row">
            <div class="cell label">Данные</div>
            <div class="cell value">
                <div class="data_jedit">
                    {DATA_JEDIT}
                </div>
            </div>
        </div>
        <!-- / -->

    </div>

    <!-- handlers_output -->
    <div class="assignments">
        {ASSIGNMENTS}
    </div>
    <!-- / -->

</div>

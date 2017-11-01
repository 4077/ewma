<div class="{__NODE_ID__} {DISABLED_CLASS}" instance="{__INSTANCE__}">

    <div class="bar" hover="hover">
        <div class="info">
            <!-- required -->
            <div class="required">R</div>
            <!-- / -->
            <!-- has_reference -->
            <div class="reference">{REFERENCE}</div>
            <!-- / -->
        </div>
        {*<div class="name">{NAME_TXT}</div>*}
        {*<div class="cb"></div>*}
        <div class="path">{PATH_TXT}</div>
        <div class="cb"></div>
        <div class="id">{ID_INFO}</div>
        <div class="cb"></div>

        <div class="cp hidden">{CP}</div>
    </div>

    <!-- if container -->
    <table class="containers">
        <!-- container -->
        <tr class="container {TYPE_CLASS} {DISABLED_CLASS}" handler_{~HANDLER_ID}_container_id="{ID}" container_id="{ID}">
            <td class="bar {TYPE_CLASS}" hover="hover" width="1">
                <div class="info">
                    <!-- container/combine_mode -->
                    <div class="combine_mode">{VALUE}</div>
                    <!-- / -->
                    <!-- container/required -->
                    <div class="required">R</div>
                    <!-- / -->
                    <!-- container/has_reference -->
                    <div class="reference">{REFERENCE}</div>
                    <!-- / -->
                    <div class="cb"></div>
                </div>
                {*<div class="name">{NAME_TXT}</div>*}
                {*<div class="cb"></div>*}
                <div class="path">{PATH_TXT}</div>
                <div class="cb"></div>
                <div class="id">{ID_INFO}</div>
                <div class="cb"></div>

                <div class="cp hidden">{CP}</div>
            </td>
            <td class="assignments">{ASSIGNMENTS}</td>
        </tr>
        <!-- / -->
    </table>
    <!-- / -->

</div>

<!-- nodes -->
<div class="nodes" module_path="{MODULE_PATH}" parent_id="{PARENT_ID}" node_type="{TYPE}">

    <!-- nodes/node -->
    <div class="node {CLASS}" hover="hover">
        <div class="buttons">
            <!-- nodes/node/update_cache -->
            <div class="action_button {nodes/node/CLASS}" hover="hover" title="update cache" action="update_cache">
                <div class="icon {CLASS}"></div>
            </div>
            <!-- / -->

            <div class="cb"></div>
        </div>

        <div class="indent {INDENT_CLICKABLE_CLASS}" hover="hover" style="width: {INDENT_WIDTH}px">
            <div class="icon {EXPAND_ICON_CLASS}"></div>
        </div>

        <div class="name" hover="hover" style="margin-left: {NAME_MARGIN_LEFT}px">{NAME}</div>

        <div class="cb"></div>
    </div>
    <!-- / -->

    <!-- nodes/subnodes -->
    <div class="subnodes{HIDDEN_CLASS}">

        <!-- nodes/subnodes/subnode -->
        {CONTENT}
        <!-- / -->

    </div>
    <!-- / -->

</div>
<!-- / -->

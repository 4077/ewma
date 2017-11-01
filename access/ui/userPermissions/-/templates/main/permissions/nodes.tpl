<!-- nodes -->
<div class="nodes {LINKED_CLASS}" node_id="{ID}" parent_id="{PARENT_ID}" node_type="{TYPE}">

    <!-- nodes/node -->
    <div class="node {CLASS} {HAS_LINKED_SUBNODES_CLASS} {EXCLUDED_CLASS}" hover="hover">
        <div class="indent {INDENT_CLICKABLE_CLASS}" hover="hover" style="width: {INDENT_WIDTH}px">
            <div class="icon {EXPAND_ICON_CLASS}"></div>
        </div>

        <div class="name" hover="hover" style="margin-left: {NAME_MARGIN_LEFT}px">{NAME}</div>

        <!-- nodes/node/toggle_action_buttons -->
        <div class="toggle_action_button override {nodes/node/ACTION_BUTTON_OVERRIDE_CLASS}" hover="hover">
            <div class="icon"></div>
        </div>
        <div class="toggle_action_button exclude {nodes/node/ACTION_BUTTON_EXCLUDE_CLASS}" hover="hover">
            <div class="icon"></div>
        </div>
        <!-- / -->

        <div class="cb"></div>
    </div>
    <!-- / -->

    <!-- nodes/subnodes -->
    <div class="subnodes {HIDDEN_CLASS}">

        <!-- nodes/subnodes/subnode -->
        {CONTENT}
        <!-- / -->

    </div>
    <!-- / -->

</div>
<!-- / -->

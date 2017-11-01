<div class="{__NODE_ID__}" instance="{__INSTANCE__}">

    <table>
        <!-- input -->
        <tr>
            <td class="label" width="1">{LABEL}</td>
            <td class="null" width="1">{NULL_BUTTON}</td>
            <td class="value {TYPE_CLASS}">
                <!-- input/string -->
                <input type="text" input_number="{INPUT_NUMBER}" value="{VALUE}">
                <!-- / -->
                <!-- input/bool -->
                {BUTTON}
                <!-- / -->
                <!-- input/data -->
                {CONTENT}
                <!-- / -->
            </td>
        </tr>
        <!-- / -->
    </table>

    <div class="buttons">
        <div class="center_wrapper">
            <div class="center_container">
                {RESET_BUTTON}
                {DISCARD_BUTTON}
                {CONFIRM_BUTTON}
            </div>
        </div>
    </div>

</div>

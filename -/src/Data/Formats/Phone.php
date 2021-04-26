<?php namespace ewma\Data\Formats;

class Phone
{
    public static function parse($phone, $lead = 7)
    {
        $integerPhone = preg_replace('/\D/', '', $phone);

        if ($integerPhone == $lead) {
            $integerPhone = '';
        }

        if ($integerPhone && substr($integerPhone, 0, 1) != $lead) {
            if (substr($integerPhone, 0, 1) == 8) {
                $integerPhone = substr($integerPhone, 1);
            }

            $integerPhone = $lead . $integerPhone;
        }

        if (strlen($integerPhone) > 11) {
            $integerPhone = substr($integerPhone, 0, 11);
        }

        return $integerPhone;
    }

    public static function format($phone, $lead = '+7')
    {
        $formattedPhone = static::phoneFormat(substr($phone, 1 - strlen($phone)), $lead . ' (###) ###-##-##');

        if (substr($formattedPhone, 0, strlen($lead)) != $lead) {
            $formattedPhone = $formattedPhone ? $lead . substr($formattedPhone, strlen($lead)) : '';
        }

        return $formattedPhone;
    }

    /**
     * https://blog.dotzero.ru/php-phone-format/
     *
     * @param        $phone
     * @param        $format
     * @param string $mask
     *
     * @return bool|string
     */
    private static function phoneFormat($phone, $format, $mask = '#')
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (is_array($format)) {
            if (array_key_exists(strlen($phone), $format)) {
                $format = $format[strlen($phone)];
            } else {
                return false;
            }
        }

        $pattern = '/' . str_repeat('([0-9])?', substr_count($format, $mask)) . '(.*)/';

        $format = preg_replace_callback(
            str_replace('#', $mask, '/([#])/'),
            function () use (&$counter) {
                return '${' . (++$counter) . '}';
            },
            $format
        );

        return $phone ? trim(preg_replace($pattern, $format, $phone, 1)) : false;
    }
}

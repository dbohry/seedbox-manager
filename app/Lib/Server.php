<?php
namespace app\Lib;

class Server extends Users
{
    const VERSION = '2.4.4';

    public static function getUptime()
    {
        $data_uptime = file_get_contents('/proc/uptime');
        $data_uptime = explode(' ', $data_uptime);
        $data_uptime = trim($data_uptime[0]);

        $time = [];
        $time['min'] = $data_uptime / 60;
        $time['hours'] = $time['min'] / 60;
        $time['days'] = floor($time['hours'] / 24);
        $time['hours'] = floor($time['hours'] - $time['days'] * 24);
        $time['min'] = floor($time['min'] - $time['days'] * 60 * 24 - $time['hours'] * 60);

        $result = '';
        if ($time['days'] != 0) {
            $result = $time['days'] . ' jours ';
        }
        if ($time['hours'] != 0) {
            $result .= $time['hours'] . ' h ';
        }
        $result .= $time['min'] . ' min';
        return $result;
    }

    public static function load_average()
    {
        $load_average = sys_getloadavg();
        for ($i=0; isset($load_average[$i]); $i++) {
            $load_average[$i] = round($load_average[$i], 2);
        }
        if ($load_average[0] < 5) {
            $info_charge = '<em class="text-success">Charge faible, conditions optimales.</em>';
        } elseif ($load_average[0] < 10) {
            $info_charge = '<em class="text-warning">Charge élévée, risque de ralentissement sur le serveur.</em>';
        } else {
            $info_charge = '<em class="text-danger">Charge très élévée, risque de gros ralentissement sur le serveur.</em>';
        }
        return ['load_average' => $load_average, 'info_charge' => $info_charge];
    }

    public function FileDownload($file_config_name, $conf_ext_prog)
    {
        file_put_contents('../conf/users/' . $this->userName . '/' . $file_config_name, $conf_ext_prog);

        set_time_limit(0);
        $path_file_name = '../conf/users/' . $this->userName . '/' . $file_config_name;
        $file_name = $file_config_name;
        $file_size = filesize($path_file_name);

        ini_set('zlib.output_compression', 0);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $file_size);
        ob_clean();
        flush();
        readfile($path_file_name);

        //delete file config (transdroid|filezilla) for security.
        unlink('../conf/users/' . $this->userName . '/' . $file_config_name);
    }

    public function CheckUpdate()
    {
        $lifetime_cookie = time() + 3600*24;
        if (!isset($_COOKIE['seedbox-manager']) && $this->is_owner === true) {
            setcookie('seedbox-manager', 'check-update', $lifetime_cookie, '/', null, false, true);
            $url_repository = 'https://raw.githubusercontent.com/Magicalex/seedbox-manager/master/version.json';
            $remote = json_decode(file_get_contents($url_repository));
            if (self::VERSION !== $remote->version) {
                $result = $remote;
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }
        return $result;
    }

    public function logout_url_redirect()
    {
        return $this->url_redirect;
    }

    public function version()
    {
        return self::VERSION;
    }
}

<?php

namespace dokuwiki\plugin\bez\mdl;

use dokuwiki\plugin\bez\meta\PermissionDeniedException;

class Authentication_tokenFactory extends Factory {

    public function can_create_token() {
        return $this->model->get_level() >= BEZ_AUTH_USER;
    }

    public function get_token($page_id) {
        $r = $this->model->sqlite->query("SELECT token FROM {$this->get_table_name()} WHERE page_id=?", $page_id);
        $token = $this->model->sqlite->res2single($r);
        if (!$token) {
            return false;
        }
        return $token;
    }

    public function create_token($page_id, $expire_date='') {

        if (!$this->can_create_token()) {
            throw new PermissionDeniedException();
        }

        $token = $this->get_token($page_id);
        if ($token) {
            return $token;
        }

        if ($expire_date == '') {
            $expire_date = date('c', strtotime('+10 years'));
        }

        $token = bin2hex(openssl_random_pseudo_bytes(16));
        $this->model->sqlite->storeEntry($this->get_table_name(),
                                         array('page_id'         => $page_id,
                                               'token'           => $token,
                                               'generated_by'    => $this->model->user_nick,
                                               'generation_date' => date('c'),
                                               'expire_date'     => $expire_date));

        return $token;
    }
}
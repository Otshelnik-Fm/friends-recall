<?php

/**
 * Friends-Recall setup
 *
 * @package FriendsRecall
 * @since   2.0
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main FriendsRecall Class.
 *
 * @class FriendsRecall
 */
final class FriendsRecall {

    /**
     * Единственный экземпляр класса.
     *
     * @var FriendsRecall
     * @since 2.0
     */
    protected static $_instance = null;

    /**
     * Считаем друзей текущего юзера
     *
     * @var int
     * @since 2.0
     */
    public $count_current_user_friends = null;

    /**
     * Считаем друзей личного кабинета
     *
     * @var int
     * @since 2.0
     */
    public $count_lk_friends = null;

    /**
     * Считаем входящие заявки в кабинет
     *
     * @var int
     * @since 2.0
     */
    public $count_incoming_lk_requests = null;

    /**
     * Связи текущего пользователя к чужому ЛК и обратно
     *
     * @var array
     * @since 2.0
     */
    public $users_relations_to_lk = null;

    /**
     * Связи текущего пользователя к автору публикации
     *
     * @var array
     * @since 2.0
     */
    public $users_relations_to_author = null;

    /**
     * Статус дружбы чужого ЛК к залогиненному (владелец ЛК - друг?)
     *
     * @var int
     * @since 2.0
     */
    public $status_lk_to_current_user = null;

    /**
     * Статус дружбы автора к залогиненному (владелец ЛК - друг?)
     *
     * @var int
     * @since 2.0
     */
    public $status_author_to_current_user = null;

    /**
     * Экземпляр класса FriendsRecall.
     *
     * Обеспечивает единственную загрузку экземпляра класса.
     *
     * @since 2.0
     * @static
     * @see frnd_base()
     * @return FriendsRecall - Экземпляр класса.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();

        do_action( 'frnd_loaded' );
    }

    /**
     * Hook into actions and filters.
     *
     * @since 2.0
     */
    private function init_hooks() {
        if ( is_user_logged_in() && $this->is_request( 'frontend' ) ) {
            add_action( 'init', array( $this, 'get_count_current_user_friends' ), 4 );
            add_action( 'wp', array( $this, 'get_relation_current_user_friendship_to_author' ), 1 );
            add_action( 'wp', array( $this, 'get_status_author_to_current_user' ), 1 );

            if ( $this->is_request( 'not_my_office' ) ) {
                add_action( 'init', array( $this, 'get_relation_current_user_friendship' ), 5 );
                add_action( 'init', array( $this, 'get_status_lk_to_current_user' ), 5 );
            }
        }

        if ( $this->is_request( 'office' ) ) {
            add_action( 'init', array( $this, 'get_count_lk_friends' ), 4 );
            if ( is_user_logged_in() ) {
                add_action( 'init', array( $this, 'get_count_incoming_lk_requests' ), 4 );
            }

            add_action( 'rcl_enqueue_scripts', array( $this, 'load_template_style' ) );
            add_action( 'rcl_enqueue_scripts', array( $this, 'load_core_style' ) );
        }
    }

    /**
     * Какой тип запроса?
     *
     * @param  string $type admin, office, not_my_office или frontend.
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin':
                return is_admin();

            // в кабинете
            case 'office':
                return rcl_is_office();

            // в кабинете, но не в своем
            case 'not_my_office':
                global $user_ID;

                return ( rcl_is_office() && ! rcl_is_office( $user_ID ) );

            // не в админке, но ajax загрузка любой вкладки
            case 'frontend':
                return ( ! is_admin() || (isset( $_POST['action'] ) && $_POST['action'] == 'rcl_ajax_tab') );
        }
    }

    // Инициализация констант
    private function define_constants() {
        global $wpdb;

        // абсолютный путь до папки дополнения друзей с слешем на конце
        $this->define( 'FRND_ADDON_ABSPATH', dirname( FRND_ADDON_FILE ) . '/' );

        // урл до папки дополнения друзей с слешем на конце
        $this->define( 'FRND_ADDON_URL', rcl_get_url_current_addon( FRND_ADDON_ABSPATH ) . '/' );

        // имя таблицы 'wp_otfm_friends'
        $this->define( 'FRND_DB', $wpdb->base_prefix . 'otfm_friends' );

        // имя таблицы 'wp_otfm_friends_messages'
        $this->define( 'FRND_MESS_DB', $wpdb->base_prefix . 'otfm_friends_messages' );
    }

    /**
     * Определяем константы если нет.
     *
     * @param string        $name  Constant name.
     * @param string|bool   $value Constant value.
     */
    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
     * Точка подключения файлов.
     */
    public function includes() {
        if ( $this->is_request( 'admin' ) ) {
            include_once FRND_ADDON_ABSPATH . 'inc/settings.php';
        }
        // только в ЛК
        if ( $this->is_request( 'office' ) ) {
            //include_once FRND_ADDON_ABSPATH . 'classes/class-friends-top-messages.php';
        }
    }

    // основные стили
    public function load_core_style() {
        rcl_enqueue_style( 'frnd_core_style', rcl_addon_url( 'assets/css/friends-core.css', __FILE__ ) );
    }

    // основные стили для залогиненого
    public function load_logged_in_style() {
        rcl_enqueue_style( 'frnd_core_logged_style', rcl_addon_url( 'assets/css/friends-logged-in.css', __FILE__ ) );
    }

    // основной скрипт
    public function load_logged_in_script() {
        rcl_enqueue_script( 'frnd_core_script', rcl_addon_url( 'assets/js/friends-logged-in.js', __FILE__ ), false, true );
    }

    // стили шаблона "Карточкой"
    public function load_card_style() {
        rcl_enqueue_style( 'frnd_card', rcl_addon_url( 'assets/css/friends-card.css', __FILE__ ) );
    }

    // стили шаблона "Мини карточкой"
    public function load_mini_card_style() {
        rcl_enqueue_style( 'frnd_mini_card', rcl_addon_url( 'assets/css/friends-mini-card.css', __FILE__ ) );
    }

    // стили шаблона "Аватаркой"
    public function load_ava_style() {
        rcl_enqueue_style( 'frnd_ava', rcl_addon_url( 'assets/css/friends-ava.css', __FILE__ ) );
    }

    // шаблон вывода друзей
    public function load_template_style() {
        // нет друзей - не нужны стили шаблона
        if ( ! $this->count_lk_friends )
            return;

        $type = rcl_get_option( 'frnd_type', 'frnd-mini-card' );

        switch ( $type ) {
            case 'frnd-card':
                $this->load_card_style();
                break;
            case 'frnd-mini-card':
                $this->load_mini_card_style();
                break;
            case 'frnd-ava':
                $this->load_ava_style();
                break;
        }
    }

    /**
     * Получим кол-во друзей текущего пользователя
     *
     * @since 2.0
     *
     * @return int  Число друзей ('0' - нет друзей)
     *
     */
    public function get_count_current_user_friends() {
        global $user_ID;

        $count = get_user_meta( $user_ID, 'frnd_total_friends', true );

        if ( $count === '0' || $count > 0 ) {
            $this->count_current_user_friends = $count;
        } else {
            $this->count_current_user_friends = ( int ) frnd_count_user_friends( $user_ID );
        }
    }

    /**
     * Получим кол-во друзей у кабинета
     *
     * @since 2.0
     *
     * @return int  Число друзей ('0' - нет друзей)
     *
     */
    public function get_count_lk_friends() {
        global $rcl_office, $user_ID;

        // в своем ЛК
        if ( $rcl_office == $user_ID ) {
            $this->count_lk_friends = $this->count_current_user_friends;
        } else {
            $count = get_user_meta( $rcl_office, 'frnd_total_friends', true );

            if ( $count === '0' || $count > 0 ) {
                $this->count_lk_friends = $count;
            } else {
                $this->count_lk_friends = ( int ) frnd_count_user_friends( $rcl_office );
            }
        }
    }

    /**
     * Считаем входящие заявки в кабинет
     *
     * @since 2.0
     *
     * @return int  Число входящих запросов ('null' - нет)
     *
     */
    public function get_count_incoming_lk_requests() {
        global $rcl_office, $user_ID;

        // в своем ЛК
        if ( $rcl_office == $user_ID ) {
            $count = get_user_meta( $user_ID, 'frnd_incoming_call', true );

            if ( $count === '0' || $count > 0 ) {
                $this->count_incoming_lk_requests = $count;
            } else {
                $this->count_incoming_lk_requests = frnd_count_incoming_friend_requests( $rcl_office );
            }
        }
    }

    /**
     * Связи одного к другому в ЛК
     *
     * @since 2.0
     *
     * @return array
     *
     */
    public function get_relation_current_user_friendship() {
        global $rcl_office, $user_ID;

        // связи обоих
        if ( ! $this->users_relations_to_lk ) {
            $this->users_relations_to_lk = frnd_get_relation_friendship( $user_ID, $rcl_office );
        }
    }

    /**
     * Получим статус дружбы чужого ЛК к залогиненному (владелец ЛК - друг?)
     *
     * @since 2.0
     *
     * @return int
     *
     */
    public function get_status_lk_to_current_user() {
        global $user_ID;

        // в чужом ЛК работает
        $relations = $this->users_relations_to_lk;

        // статус дружбы в чужом ЛК и залогиненному
        if ( ! $this->status_lk_to_current_user ) {
            if ( isset( $relations[0] ) && $relations[0]['owner_id'] == $user_ID ) {
                $this->status_lk_to_current_user = ( int ) $relations[0]['status'];
            } else if ( isset( $relations[1] ) && $relations[1]['owner_id'] == $user_ID ) {
                $this->status_lk_to_current_user = ( int ) $relations[1]['status'];
            }
        }
    }

    /**
     * Связи одного к другому в одиночной записи
     *
     * @since 2.0
     *
     * @return array
     *
     */
    public function get_relation_current_user_friendship_to_author() {
        // это не одиночная запись
        if ( rcl_is_office() || is_singular( 'page' ) || is_front_page() )
            return;

        global $user_ID;

        // связи обоих
        if ( ! $this->users_relations_to_author ) {
            global $post;

            if ( $user_ID != $post->post_author ) {
                $this->users_relations_to_author = frnd_get_relation_friendship( $user_ID, $post->post_author );
            }
        }
    }

    /**
     * Получим статус дружбы автора к залогиненному (автор публикации - друг?)
     *
     * @since 2.0
     *
     * @return int
     *
     */
    public function get_status_author_to_current_user() {
        // это не одиночная запись
        if ( rcl_is_office() || is_singular( 'page' ) || is_front_page() )
            return;

        global $user_ID;

        $relations = $this->users_relations_to_author;

        // статус дружбы в чужом ЛК и залогиненному
        if ( ! $this->status_author_to_current_user ) {
            global $post;

            // юзер = автор публикации
            if ( $user_ID == $post->post_author )
                return;

            if ( isset( $relations[0] ) && $relations[0]['owner_id'] == $user_ID ) {
                $this->status_author_to_current_user = ( int ) $relations[0]['status'];
            } else if ( isset( $relations[1] ) && $relations[1]['owner_id'] == $user_ID ) {
                $this->status_author_to_current_user = ( int ) $relations[1]['status'];
            }
        }
    }

}

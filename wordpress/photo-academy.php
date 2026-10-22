<?php
/**
 * Plugin Name: Photo Academy Landing
 * Description: Landing page autonome et collecte privée des demandes d'inscription.
 * Version: 1.0.0
 */
if (!defined('ABSPATH')) exit;
add_action('init', function () {
    register_post_type('pa_lead', ['label'=>'Demandes Photo Academy', 'public'=>false, 'show_ui'=>true,
        'supports'=>['title','editor'], 'capability_type'=>'post', 'capabilities'=>[
            'edit_posts'=>'manage_options','edit_others_posts'=>'manage_options','publish_posts'=>'manage_options',
            'read_private_posts'=>'manage_options','delete_posts'=>'manage_options','create_posts'=>'do_not_allow'
        ], 'map_meta_cap'=>true]);
});
add_shortcode('photo_academy', function () {
    $base = plugin_dir_url(__FILE__);
    wp_enqueue_style('photo-academy', $base.'assets/style.css', [], '1.0.0');
    wp_enqueue_script('photo-academy', $base.'assets/app.js', [], '1.0.0', true);
    $privacy = get_privacy_policy_url();
    wp_add_inline_script('photo-academy', 'window.PHOTO_ACADEMY_CONFIG = '.wp_json_encode([
        'endpoint'=>rest_url('photo-academy/v1/leads'), 'nonce'=>wp_create_nonce('wp_rest'),
        'privacyUrl'=>$privacy, 'trackingConsent'=>false
    ]).';', 'before');
    $html = file_get_contents(__DIR__.'/index.html');
    preg_match('~<body>(.*)</body>~s', $html, $matches);
    return $matches[1] ?? '';
});
add_action('rest_api_init', function () {
    register_rest_route('photo-academy/v1', '/leads', ['methods'=>'POST', 'permission_callback'=>'__return_true', 'callback'=>function (WP_REST_Request $request) {
        if (!get_privacy_policy_url()) return new WP_Error('pa_setup', 'Politique de confidentialité manquante.', ['status'=>503]);
        // Nonce anti-CSRF public ; ce n'est pas une authentification ni un antispam.
        if (!wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest')) return new WP_Error('pa_nonce', 'Rechargez la page puis réessayez.', ['status'=>403]);
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown'; // Ne pas faire confiance à X-Forwarded-For.
        $key = 'pa_limit_'.hash_hmac('sha256', $ip, wp_salt('nonce'));
        $attempts = (int)get_transient($key);
        if ($attempts >= 5) return new WP_Error('pa_rate', 'Trop de tentatives. Réessayez plus tard.', ['status'=>429]);
        set_transient($key, $attempts + 1, 10 * MINUTE_IN_SECONDS);
        $p = $request->get_json_params();
        if (!is_array($p) || strlen($request->get_body()) > 4096 || !empty($p['website'])) return new WP_Error('pa_invalid', 'Demande invalide.', ['status'=>400]);
        $name = sanitize_text_field($p['fullName'] ?? '');
        $phone = sanitize_text_field($p['phone'] ?? '');
        $email = sanitize_email($p['email'] ?? '');
        $level = $p['level'] ?? '';
        $goal = $p['goal'] ?? '';
        if (strlen($name)<2 || strlen($name)>100 || !preg_match('/^[+0-9 ()\-]{8,22}$/', $phone) || !is_email($email) || strlen($email)>150 ||
            !in_array($level,['Débutant','Amateur','Intermédiaire'],true) || !in_array($goal,['Passion personnelle','Projet professionnel'],true) ||
            ($p['priceConsent'] ?? false)!==true || ($p['contactConsent'] ?? false)!==true) {
            return new WP_Error('pa_fields', 'Vérifiez les champs et les consentements.', ['status'=>422]);
        }
        $id = wp_insert_post(['post_type'=>'pa_lead', 'post_status'=>'private', 'post_title'=>$name,
            'post_content'=>"WhatsApp : $phone\nE-mail : $email\nNiveau : $level\nObjectif : $goal\nTarif accepté : 5 800 DH / 2 mois\nConsentement de contact : oui\nPolitique : ".get_privacy_policy_url()."\nDate UTC : ".gmdate('c')], true);
        if (is_wp_error($id) || !$id) return new WP_Error('pa_storage', 'Enregistrement impossible.', ['status'=>500]);
        // Réception confirmée uniquement après stockage durable. Pas de dépendance à l'e-mail.
        return new WP_REST_Response(['success'=>true, 'id'=>(string)$id], 201);
    }]);
});

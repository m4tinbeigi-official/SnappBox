<?php
declare(strict_types=1);

namespace Snappbox\API;

/**
 * Remote Broadcast service for communicating with users via GitHub.
 */
class Broadcast {

    private const REMOTE_URL = 'https://raw.githubusercontent.com/m4tinbeigi-official/SnappBox/main/broadcast.json';
    private const TRANSIENT_KEY = 'snappbox_remote_broadcast';

    /**
     * Boot the service (called by DI Container).
     */
    public function boot(): void {
        \add_action('admin_notices', [$this, 'render_broadcast']);
    }

    /**
     * Fetch the broadcast from GitHub (with caching).
     */
    public function get_latest(): ?array {
        $data = \get_transient(self::TRANSIENT_KEY);

        if (false === $data) {
            $response = \wp_remote_get(self::REMOTE_URL);

            if (\is_wp_error($response)) {
                return null;
            }

            $body = \wp_remote_retrieve_body($response);
            $data = \json_decode($body, true);

            if ($data) {
                \set_transient(self::TRANSIENT_KEY, $data, 12 * \HOUR_IN_SECONDS);
            }
        }

        return $data;
    }

    /**
     * Render the broadcast notice in WordPress Admin.
     */
    public function render_broadcast(): void {
        $broadcast = $this->get_latest();

        if (!$broadcast || !isset($broadcast['active']) || !$broadcast['active']) {
            return;
        }

        $type = $broadcast['type'] ?? 'info';
        $title = $broadcast['title'] ?? '';
        $content = $broadcast['content'] ?? '';
        $image = $broadcast['image'] ?? '';
        $button_text = $broadcast['button']['text'] ?? '';
        $button_url = $broadcast['button']['url'] ?? '';

        ?>
        <div class="notice notice-<?php echo \esc_attr($type); ?> is-dismissible snappbox-elite-broadcast" style="padding: 0; border: none; background: transparent;">
            <div class="elite-card" style="display: flex; gap: 24px; align-items: center; margin: 20px 0; max-width: 900px; padding: 24px;">
                <?php if ($image): ?>
                    <div style="flex-shrink: 0;">
                        <img src="<?php echo \esc_url($image); ?>" style="width: 120px; height: 120px; object-fit: cover; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);" alt="Broadcast">
                    </div>
                <?php endif; ?>
                
                <div style="flex-grow: 1;">
                    <h3 style="margin: 0 0 8px 0; color: #15161a; font-size: 1.25rem; font-weight: 700;"><?php echo \esc_html($title); ?></h3>
                    <p style="margin: 0 0 16px 0; color: #5c5d61; font-size: 0.95rem; line-height: 1.5;"><?php echo \esc_html($content); ?></p>
                    
                    <?php if ($button_text && $button_url): ?>
                        <a href="<?php echo \esc_url($button_url); ?>" target="_blank" class="elite-button" style="display: inline-block; text-decoration: none;">
                            <?php echo \esc_html($button_text); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}

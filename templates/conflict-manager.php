<?php
global $wpdb;
$ct = $wpdb->prefix . 'booking_conflicts';
$rows = $wpdb->get_results("SELECT * FROM {$ct} WHERE is_resolved = 0 ORDER BY created_at DESC");
?>
<div class="wrap">
    <h1>Conflict Manager</h1>
    <?php if (empty($rows)) : ?>
        <p>No unresolved conflicts.</p>
    <?php else: ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Booking ID</th>
                    <th>Type</th>
                    <th>Details</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r):
                    $data = json_decode($r->data, true);
                    $a = isset($data['api_a']) ? $data['api_a'] : null;
                    $b = isset($data['api_b']) ? $data['api_b'] : null;
                ?>
                    <tr>
                        <td><?php echo esc_html($r->id); ?></td>
                        <td><?php echo esc_html($r->booking_id); ?></td>
                        <td><?php echo esc_html($r->conflict_type); ?></td>
                        <td style="max-width:480px;">
                            <?php if ($a && $b): ?>
                                <div style="display:flex;gap:10px;">
                                    <div style="flex:1;"><strong>API A</strong>
                                        <pre><?php echo esc_html(json_encode($a, JSON_PRETTY_PRINT)); ?></pre>
                                    </div>
                                    <div style="flex:1;"><strong>API B</strong>
                                        <pre><?php echo esc_html(json_encode($b, JSON_PRETTY_PRINT)); ?></pre>
                                    </div>
                                </div>
                            <?php else: ?>
                                <pre><?php echo esc_html(json_encode($data, JSON_PRETTY_PRINT)); ?></pre>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div>
                                <label><input type="radio" name="choice_<?php echo esc_attr($r->id); ?>" value="A"> Accept A</label><br />
                                <label><input type="radio" name="choice_<?php echo esc_attr($r->id); ?>" value="B"> Accept B</label><br />
                                <label><input type="radio" name="choice_<?php echo esc_attr($r->id); ?>" value="manual"> Manual override</label><br />
                                <input type="text" id="manual_<?php echo esc_attr($r->id); ?>" placeholder="Manual price (optional)"><br />
                                <textarea id="note_<?php echo esc_attr($r->id); ?>" placeholder="Resolution note"></textarea><br />
                                <button class="button wfa-resolve-btn" data-id="<?php echo esc_attr($r->id); ?>">Resolve</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
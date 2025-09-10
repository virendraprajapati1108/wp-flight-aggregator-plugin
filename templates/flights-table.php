<?php if (empty($flights)) : ?>
    <p>No flights found for selected route/date.</p>
<?php else: ?>
    <table class="wfa-table widefat">
        <thead>
            <tr>
                <th>Flight</th>
                <th>Flight No</th>
                <th>Price</th>
                <th>Depart</th>
                <th>Source</th>
                <th>Seats</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($flights as $f):
                $fid = isset($f['flight_id']) ? esc_html($f['flight_id']) : '';
                $fno = isset($f['flight_number']) ? esc_html($f['flight_number']) : '';
                $price = isset($f['price']) ? esc_html($f['price']) : '';
                $depart = isset($f['departure_time']) ? esc_html($f['departure_time']) : '';
                $source = isset($f['source']) ? esc_html($f['source']) : '';
                $seats = isset($f['available_seats']) ? esc_html($f['available_seats']) : '';
                $json = esc_attr(wp_json_encode($f));
            ?>
                <tr>
                    <td><?php echo $fid; ?></td>
                    <td><?php echo $fno; ?></td>
                    <td><?php echo $price; ?></td>
                    <td><?php echo $depart; ?></td>
                    <td><?php echo $source; ?></td>
                    <td><?php echo $seats; ?></td>
                    <td><button class="wfa-book-btn" data-flight="<?php echo $json; ?>">Book Now</button></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div id="wfa-booking-form" style="display:none;margin-top:1rem;border:1px solid #ddd;padding:10px;">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('wfa_book', 'wfa_book_nonce'); ?>
            <input type="hidden" name="action" value="wfa_save_booking" />
            <input type="hidden" name="flight_json" value="" />
            <p>
                <label>Name: <input type="text" name="passenger_name" required /></label><br />
                <label>Email: <input type="email" name="email" required /></label><br />
                <label>Mobile: <input type="text" name="mobile" required /></label><br />
                <label>Seat Count: <input type="number" name="seat_count" min="1" value="1" /></label>
            </p>
            <p><button type="submit">Confirm Booking</button> <button type="button" id="wfa-cancel">Cancel</button></p>
        </form>
    </div>

    <script>
        (function() {
            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('wfa-book-btn')) {
                    var f = e.target.getAttribute('data-flight');
                    var wrap = document.getElementById('wfa-booking-form');
                    wrap.style.display = 'block';
                    wrap.querySelector('input[name="flight_json"]').value = f;
                    wrap.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
                if (e.target && e.target.id === 'wfa-cancel') {
                    document.getElementById('wfa-booking-form').style.display = 'none';
                }
            });
        })();
    </script>
<?php endif; ?>
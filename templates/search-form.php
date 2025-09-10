<?php
// simple hardcoded dropdowns as allowed by spec
?>
<form method="get" class="wfa-search">
    <label>Origin:
        <select name="wfa_origin">
            <option value="DEL">DEL</option>
            <option value="BOM">BOM</option>
            <option value="BLR">BLR</option>
            <option value="HYD">HYD</option>
        </select>
    </label>
    <label>Destination:
        <select name="wfa_destination">
            <option value="BOM">BOM</option>
            <option value="DEL">DEL</option>
            <option value="BLR">BLR</option>
            <option value="HYD">HYD</option>
        </select>
    </label>
    <label>Date:
        <input type="date" name="wfa_date" required />
    </label>
    <button type="submit">Search Flights</button>
</form>

<?php if (isset($_GET['wfa_booked']) && $_GET['wfa_booked'] == '1') : ?>
    <div class="wfa-notice">Booking saved successfully.</div>
<?php endif; ?>
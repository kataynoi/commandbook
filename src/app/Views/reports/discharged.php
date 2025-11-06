
<h3>รายงานจำนวนผู้ป่วยที่ละทะเบียน</h3>
<form method="get" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="ampurcodefull" class="form-select" onchange="this.form.submit()">
            <option value="">-- เลือกอำเภอ --</option>
            <?php foreach ($ampurs as $a): ?>
                <option value="<?= $a['ampurcodefull'] ?>" <?= $selected['ampur'] == $a['ampurcodefull'] ? 'selected' : '' ?>>
                    <?= $a['ampurname'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select name="tamboncodefull" class="form-select" onchange="this.form.submit()">
            <option value="">-- เลือกตำบล --</option>
            <?php foreach ($tambons as $t): ?>
                <option value="<?= $t['tamboncodefull'] ?>" <?= $selected['tambon'] == $t['tamboncodefull'] ? 'selected' : '' ?>>
                    <?= $t['tambonname'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select name="villagecode" class="form-select" onchange="this.form.submit()">
            <option value="">-- เลือกหมู่บ้าน --</option>
            <?php foreach ($villages as $v): ?>
                <option value="<?= $v['villagecodefull'] ?>" <?= $selected['village'] == $v['villagecodefull'] ? 'selected' : '' ?>>
                    <?= $v['villagename'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>อำเภอ</th>
            <th>ตำบล</th>
            <th>หมู่บ้าน</th>
            <?php foreach ($riskLevels as $r): ?>
                <th><?= $r['name'] ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($areaMap as $row): ?>
            <tr>
                <td><?= esc($row['ampur']) ?></td>
                <td><?= esc($row['tambon']) ?></td>
                <td><?= esc($row['village']) ?></td>
                <?php foreach ($riskLevels as $r): ?>
                    <td><?= $row['risk_' . $r['id']] ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
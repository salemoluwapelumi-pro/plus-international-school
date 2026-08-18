<?php
$pageTitle = 'Apply Online';
$activeNav = 'admissions';
require __DIR__ . '/../partials/header.php';
$classes = [];
try { $classes = classes_list(); } catch (Throwable $e) { $classes = []; }
?>
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="<?= url('index.php') ?>">Home</a> · Admissions · Apply online</div>
        <h1>Online application form</h1>
        <p>Complete the form below and our admissions office will contact you within two working days.</p>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:860px">
        <div class="card reveal">
            <form data-ajax action="<?= url('backend/api/admissions/apply.php') ?>" method="post">
                <div class="form-grid">
                    <div class="field"><label for="a-child">Child's full name</label><input id="a-child" name="child_name" required></div>
                    <div class="field"><label for="a-dob">Date of birth</label><input id="a-dob" type="date" name="date_of_birth"></div>
                    <div class="field">
                        <label for="a-class">Class applying for</label>
                        <select id="a-class" name="class_applied" required>
                            <option value="">Select class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= e($class['name']) ?>"><?= e($class['name']) ?></option>
                            <?php endforeach; ?>
                            <?php if (!$classes): ?>
                                <option>Nursery 1</option><option>Primary 1</option><option>JSS 1</option><option>SS 1</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="field"><label for="a-parent">Parent / guardian name</label><input id="a-parent" name="parent_name" required></div>
                    <div class="field"><label for="a-email">Email address</label><input id="a-email" type="email" name="email" required></div>
                    <div class="field"><label for="a-phone">Phone number</label><input id="a-phone" name="phone" required></div>
                    <div class="field field-full"><label for="a-address">Home address</label><textarea id="a-address" name="address" rows="3"></textarea></div>
                </div>
                <button class="btn btn-primary" type="submit">Submit application</button>
            </form>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>

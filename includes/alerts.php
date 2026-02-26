<!-- Password Change Warning Banner -->
<?php if (isset($need_password_change) && $need_password_change): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-exclamation-triangle"></i> Security Warning!</strong>
        You are using the default password. Please <a href="change_password.php" class="alert-link">change your password</a> immediately for security reasons.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Success Message -->
<?php if (isset($success_msg) && $success_msg): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-check-circle"></i> Success!</strong> <?php echo htmlspecialchars($success_msg); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Error Message -->
<?php if (isset($error_msg) && $error_msg): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-exclamation-triangle"></i> Error!</strong> <?php echo htmlspecialchars($error_msg); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Info Message -->
<?php if (isset($info_msg) && $info_msg): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-info-circle"></i> Info!</strong> <?php echo htmlspecialchars($info_msg); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

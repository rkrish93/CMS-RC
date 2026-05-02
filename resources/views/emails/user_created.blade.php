<h2 style="color:#4B49AC;">Clinic Management System (CMS-RC)</h2>

<p>Dear {{ $user->fname }} {{ $user->lname }},</p>

<p>Your account has been created successfully.</p>

<hr>

<p><strong>Email:</strong> {{ $user->email }}</p>
<p><strong>Password:</strong> {{ $password }}</p>

<hr>

<p>
    Please login to your account and <strong>change your password immediately</strong> for security reasons.
</p>

<br>

<p>Thank you,<br>
CMS-RC Team</p>

<div style="margin:0;padding:24px;background:#f6f8fb;font-family:Arial,sans-serif;color:#152033;">
    <div style="max-width:620px;margin:0 auto;background:#ffffff;border:1px solid #e4e9f0;border-radius:14px;overflow:hidden;">
        <div style="padding:24px;background:#2563eb;color:#ffffff;">
            <h2 style="margin:0;font-size:22px;">CMS-RC</h2>
            <p style="margin:6px 0 0;">Clinic Management System for Rural Clinics</p>
        </div>

        <div style="padding:26px;">
            <p>Dear {{ $user->fname }} {{ $user->lname }},</p>

            <p>Your account has been created successfully. Use the credentials below to sign in.</p>

            <div style="padding:16px;border:1px solid #e4e9f0;border-radius:10px;background:#f8fafc;">
                <p style="margin:0 0 8px;"><strong>Email:</strong> {{ $user->email }}</p>
                <p style="margin:0;"><strong>Temporary Password:</strong> {{ $password }}</p>
            </div>

            <p style="margin-top:20px;">Please change your password immediately after your first login.</p>

            <p style="margin-top:24px;">Thank you,<br>CMS-RC Team</p>
        </div>
    </div>
</div>

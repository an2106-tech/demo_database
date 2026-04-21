# ✅ Offer Approval Workflow - Implementation Complete

## 📋 What Was Implemented

Your recruitment system now has a **complete offer approval workflow** where:

1. **HR creates offers** in `awaiting_approval` status
2. **HR sends offers** to branch directors for review
3. **Directors review** offers in a dedicated Filament interface
4. **Directors can approve** (auto-sends to candidate) or **reject** (keeps for HR revision)
5. **Salary visibility** is controlled by role (HR & Directors see all, PM doesn't see salary)
6. **Email notifications** are sent at each stage

---

## 🗂️ Files Created & Modified

### New Files Created:
```
✅ database/migrations/2026_04_21_update_offers_table_add_approval_flow.php
✅ app/Services/OfferApprovalService.php
✅ app/Mail/OfferApprovalRequestMail.php
✅ app/Mail/OfferApprovedNotificationMail.php
✅ app/Filament/Resources/OfferResource.php
✅ app/Filament/Resources/Pages/EditOffer.php
✅ resources/views/emails/offer-approval-request.blade.php
✅ resources/views/emails/offer-approved-notification.blade.php
✅ OFFER_APPROVAL_WORKFLOW.md (detailed technical docs)
✅ QUY_TRINH_DUYET_OFFER.md (user guide in Vietnamese)
```

### Files Modified:
```
✅ app/Models/Offer.php
  - Added: approval_requested_at, approved_by_user_id, approved_at, approval_notes
  - Added: approvedByUser() relationship
  
✅ app/Filament/Resources/Applications/Tables/ApplicationsTable.php
  - Changed: Offer creation status to 'awaiting_approval'
  - Added: sendOfferForApproval() method
  - Added: sendOfferToCandidate() method
  - Updated: send_offer action to handle new workflow
  - Updated: canSendOffer() logic
```

---

## 📊 Offer Status Lifecycle

```
NEW OFFER           → AWAITING APPROVAL      → PENDING (if approved)    → ACCEPTED/DECLINED
(when created)         (when HR submits)       (when director approves)    (candidate response)
                                           ↘ REJECTED (if director rejects)
                                               ↓
                                           AWAITING APPROVAL (HR resubmits)
```

---

## 🎯 Key Features

### For HR/PM:
- ✅ Create and edit offers easily
- ✅ Send offers for director approval (new button "Gửi duyệt")
- ✅ Edit rejected offers and resubmit
- ✅ See full salary information
- ✅ Track offer status in applications table

### For Directors:
- ✅ New menu item "Duyệt Offer" (Review Offers)
- ✅ Table view of all pending offers for their branch
- ✅ Detailed offer review page with all information
- ✅ Approve or reject buttons
- ✅ See full salary information

### For the System:
- ✅ Automatic email to candidate only after director approves
- ✅ Role-based salary visibility in emails
- ✅ Candidate receives PDF offer attached to email
- ✅ Application status stays "offer" if director rejects
- ✅ Full audit trail with timestamps and who approved/rejected

---

## 🚀 Next Steps - What You Need to Do

### 1. Run the Migration
```bash
php artisan migrate
```
This adds the new fields to the offers table.

### 2. Test the Workflow
Follow the step-by-step guide in **QUY_TRINH_DUYET_OFFER.md** (Vietnamese guide)

### 3. Configure Mail Settings (if not done)
Ensure your `.env` has proper mail configuration:
```
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@company.com
MAIL_FROM_NAME="Your Company"
```

### 4. Test Email Sending
- HR creates an offer
- HR clicks "Gửi duyệt"
- Check if director receives the email
- Director reviews and approves/rejects
- Check if emails are sent correctly

---

## 🔍 Database Schema Changes

The `offers` table now has:

```sql
ALTER TABLE offers ADD COLUMN approval_requested_at TIMESTAMP NULL;
ALTER TABLE offers ADD COLUMN approved_by_user_id BIGINT UNSIGNED NULL;
ALTER TABLE offers ADD COLUMN approved_at TIMESTAMP NULL;
ALTER TABLE offers ADD COLUMN approval_notes TEXT NULL;
ALTER TABLE offers MODIFY status ENUM('awaiting_approval', 'pending', 'accepted', 'declined', 'rejected', 'expired');
```

---

## 📧 Email Templates

### Email 1: Request for Approval
**To:** Director(s)  
**When:** HR clicks "Gửi duyệt"  
**Content:** Offer details + link to approve page

### Email 2: Offer to Candidate
**To:** Candidate  
**When:** Director clicks "Duyệt"  
**Content:** Full offer details + PDF + Accept/Decline links

### Email 3: Team Notification
**To:** HR, PM, Directors  
**When:** Director clicks "Duyệt"  
**Content:** Approval notification (salary hidden for PM)

---

## 🔐 Security & Access Control

- ✅ Directors can only see offers for their own branch
- ✅ HR cannot bypass director approval
- ✅ Candidates cannot respond to offers in `awaiting_approval` or `rejected` status
- ✅ Salary information is role-based controlled
- ✅ All actions are logged with timestamps and user info

---

## 📝 Offer Status Descriptions

| Status | Description | Next Action |
|--------|-------------|------------|
| **awaiting_approval** | HR created, waiting for director approval | Director: Approve or Reject |
| **pending** | Director approved, waiting for candidate response | Candidate: Accept or Decline |
| **accepted** | Candidate accepted the offer | Complete |
| **declined** | Candidate declined | HR: Can create new offer |
| **rejected** | Director rejected the offer | HR: Edit & resubmit |
| **expired** | Candidate didn't respond in time | HR: Can resend or create new |

---

## 🐛 Troubleshooting

### Issue: Directors don't see "Duyệt Offer" menu
**Solution:** Ensure the user has 'director' role assigned in Filament Shield

### Issue: Emails not sending
**Solution:** Check mail configuration in `.env` and test with Laravel Tinker:
```php
php artisan tinker
Mail::to('test@example.com')->send(new TestMail());
```

### Issue: Salary showing for PM in email
**Solution:** Check the role in `OfferApprovedNotificationMail` - it should hide salary for PM

### Issue: Director can see all branches' offers
**Solution:** Ensure the `branchScopeId()` method is returning the correct branch_id

---

## 📚 Documentation Files

Two comprehensive guides have been created:

1. **OFFER_APPROVAL_WORKFLOW.md** - Technical documentation (English)
   - Complete workflow diagrams
   - File structure and changes
   - Implementation notes
   - Testing checklist

2. **QUY_TRINH_DUYET_OFFER.md** - User guide (Vietnamese)
   - Step-by-step instructions
   - Screenshots descriptions
   - Role-based permissions
   - Troubleshooting tips

---

## ✨ Summary

The offer approval system is **fully implemented and ready to use**. The system now ensures:

- ✅ Controlled offer workflow with director approval
- ✅ Proper email notifications at each stage
- ✅ Role-based salary visibility
- ✅ Ability to revise and resubmit offers
- ✅ Automatic candidate notification only after approval
- ✅ Full audit trail of approvals

**You're all set! Run the migration and start using the new workflow.** 🎉

---

**Questions?** Refer to the documentation files or check the code comments for more details.

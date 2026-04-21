# Offer Approval Workflow - Implementation Guide

## Overview
This document describes the new offer approval workflow that was implemented in the recruitment system. The workflow adds a director approval step before offers are sent to candidates.

## Workflow Diagram

```
┌─────────────┐
│   HR/PM     │
└──────┬──────┘
       │
       │ 1. Create/Edit Offer
       │    Status: awaiting_approval
       ▼
┌──────────────────────────────┐
│ Offer Form (Applications)    │
│ - Salary                     │
│ - Start Date                 │
│ - Probation Period           │
│ - Content/Notes              │
└──────┬───────────────────────┘
       │
       │ 2. Send for Approval
       │    (Click "Gửi duyệt")
       ▼
    ┌──────────────────────────────┐
    │ Email to Director(s)         │
    │ OfferApprovalRequestMail     │
    │ - View details link          │
    │ - All info visible           │
    └──────┬───────────────────────┘
           │
    ┌──────▼──────────────────────┐
    │   Director (Branch)         │
    │   Reviews in Filament       │
    │   OfferResource             │
    │   - Table: Pending Offers   │
    │   - Edit: Full Details      │
    │   - Actions: Approve/Reject │
    └──────┬──────────────┬───────┘
           │              │
    Approve│              │Reject
           │              │
    ┌──────▼──────┐  ┌────▼─────────────┐
    │Status:      │  │Status: rejected   │
    │pending      │  │Keep for HR review │
    │             │  │                   │
    │3. Auto Send │  │HR can edit and    │
    │to Candidate │  │resubmit           │
    └──────┬──────┘  └───────────────────┘
           │
    ┌──────▼──────────────────────┐
    │ Email to Candidate          │
    │ CandidateOfferMail          │
    │ - PDF attached              │
    │ - Accept/Decline links      │
    │                             │
    │ Email to Team               │
    │ OfferApprovedNotificationMail│
    │ - Salary: Only HR & Director│
    │ - Info: All team members    │
    └──────┬──────────────────────┘
           │
    ┌──────▼──────────────────────┐
    │  Candidate Response         │
    │  - Accept: Status = hired   │
    │  - Decline: Status = offer  │
    └─────────────────────────────┘
```

## Database Changes

### New Migration File
`database/migrations/2026_04_21_update_offers_table_add_approval_flow.php`

**New Fields Added:**
- `approval_requested_at` (timestamp, nullable) - When approval was requested
- `approved_by_user_id` (foreign key) - User who approved/rejected
- `approved_at` (timestamp, nullable) - When approved/rejected
- `approval_notes` (text, nullable) - Notes from director

**New Offer Statuses:**
- `awaiting_approval` - HR created, waiting for director approval
- `rejected` - Director rejected, HR can revise and resubmit
- `pending` - Director approved, waiting for candidate response (existing)
- `accepted` - Candidate accepted (existing)
- `declined` - Candidate declined (existing)
- `expired` - Offer expired (existing)

## File Structure

### Models
- **`app/Models/Offer.php`** (modified)
  - Added fillable fields
  - Added casts
  - Added `approvedByUser()` relationship

### Services
- **`app/Services/OfferApprovalService.php`** (NEW)
  - `approve(Offer, User)` - Process director approval
  - `reject(Offer, User, string)` - Process director rejection
  - `notifyTeam(Offer, User)` - Send notifications to team

### Mail Classes
- **`app/Mail/OfferApprovalRequestMail.php`** (NEW)
  - Email sent to director for offer approval
  - Contains all offer details
  - Link to approval page

- **`app/Mail/OfferApprovedNotificationMail.php`** (NEW)
  - Email sent to team when director approves
  - Salary hidden for PM users
  - Shows approval status

### Filament Resources
- **`app/Filament/Resources/OfferResource.php`** (NEW)
  - Table view of pending offers
  - Filtered by director's branch
  - Quick approve/reject actions

- **`app/Filament/Resources/Pages/EditOffer.php`** (NEW)
  - Detailed offer view for director
  - Display all offer information (read-only)
  - Approve/Reject buttons in header

### Views
- **`resources/views/emails/offer-approval-request.blade.php`** (NEW)
  - Email template for director approval request
  - Professional formatting with offer details

- **`resources/views/emails/offer-approved-notification.blade.php`** (NEW)
  - Email template for team notification
  - Shows salary visibility based on user role

### Tables/Controllers
- **`app/Filament/Resources/Applications/Tables/ApplicationsTable.php`** (modified)
  - Offer creation status changed to `awaiting_approval`
  - Updated `send_offer` action to handle new workflow
  - Added `sendOfferForApproval()` method
  - Added `sendOfferToCandidate()` method
  - Updated `canSendOffer()` logic

## Key Features

### 1. Offer Creation (HR/PM)
- When creating offer → status = `awaiting_approval`
- HR can create, edit, and save multiple times
- PDF is generated automatically

### 2. Send for Approval (HR/PM)
- New button "Gửi duyệt" (Send for Approval)
- Sends email to branch directors
- Email contains link to Filament edit page
- Offer status remains `awaiting_approval`

### 3. Director Review
- Directors see menu item "Duyệt Offer" (Review Offers)
- Table shows all pending offers for their branch
- Can click "Xem chi tiết" to view full details
- Edit page shows read-only offer information

### 4. Approval Workflow
**If Director Approves:**
- Offer status → `pending`
- Auto-sends offer email to candidate (PDF attached)
- Sends approval notification to team
- Salary visible only to HR and director

**If Director Rejects:**
- Offer status → `rejected`
- Application status stays `offer` (not rejected)
- HR can edit and resubmit for director approval
- No email sent to candidate

### 5. Candidate Response
- Candidate receives email with PDF
- Can accept or decline via email links
- Links are time-limited (3 days default)
- Only works if offer status is `pending`

## Role-Based Access

### HR / PM
- Create and edit offers
- Send offers for director approval
- View all offers in applications list
- See salary information in all contexts

### Directors
- Access "Duyệt Offer" menu
- View pending offers for their branch
- Review full offer details
- Approve or reject offers
- See salary information

### Candidates
- Receive offer email when director approves
- Accept/decline via email link
- Do not see offers in any system panel

### Super Admin
- Full access to all offers
- Can view all branches' offers
- Override approval if needed

## Email Communication

### Offer Approval Request Email
**To:** Branch Directors  
**Subject:** Yêu cầu duyệt Offer – [Candidate Name] – [Job Title]  
**Content:**
- Candidate name and email
- Job title and position
- Offered salary
- Start date and probation period
- Additional notes if any
- Link to view and approve in system
- Note about approval before sending to candidate

### Offer Approved Notification
**To:** HR, PM, Directors of that branch  
**Subject:** Offer đã được duyệt – [Candidate Name] – [Job Title]  
**Content:**
- Approval confirmation
- Candidate details
- Job details
- Salary (only for HR and director recipients)
- Timeline information
- Status updates

### Offer to Candidate
**When:** After director approves  
**What:** Sends existing `CandidateOfferMail` with PDF attachment

## Implementation Notes

### Database Migration
Run the migration to add new fields:
```bash
php artisan migrate
```

### Offer Status Transitions
```
awaiting_approval → [Director Approves] → pending → [Candidate Response] → accepted/declined
                  ↘ [Director Rejects] → rejected → [HR Edits] → awaiting_approval
```

### Important Validation
- Directors can only approve offers for their branch
- HR can create offers regardless of branch (controlled elsewhere)
- Candidates can only respond to offers with `pending` status
- Expired offers are handled by existing logic

### Team Visibility
When director approves and sends to team:
- HR sees: All info including salary
- PM sees: All info except salary (hidden as ***)
- Directors see: All info including salary

## Testing Checklist

- [ ] HR can create offer (status = awaiting_approval)
- [ ] HR can send offer for director approval (button appears, email sent)
- [ ] Director receives approval request email with link
- [ ] Director can view pending offers in menu
- [ ] Director can approve offer (status → pending, sends to candidate)
- [ ] Director can reject offer (status → rejected, keeps application as offer)
- [ ] HR can edit rejected offer and resubmit
- [ ] Candidate receives email only after director approves
- [ ] Team receives notification email (check salary visibility)
- [ ] Candidate can accept/decline offer via email
- [ ] Offer links have proper expiration

## Future Enhancements

- [ ] Add approval notes from director (currently just a reject note)
- [ ] Allow multiple offer templates
- [ ] Track approval history/audit trail
- [ ] Send reminders to directors for pending approvals
- [ ] Add approval deadline feature
- [ ] Integration with payroll for salary validation
- [ ] Bulk approval for multiple offers

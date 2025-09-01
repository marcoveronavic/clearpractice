{{-- A minimal modal used on CH Search results --}}
<div id="ch-company-modal" class="modal" style="display:none;">
    <div class="modal-dialog" role="document" style="max-width: 860px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ch-company-title">
                    <span data-ch="company_name">Company Name</span>
                </h5>

                <button id="btn-add-company"
                        type="button"
                        class="btn btn-sm btn-primary"
                        title="Save to My Companies"
                        style="margin-left:12px;"
                        data-company-number="">
                    Add to my companies
                </button>

                <button type="button" class="close" aria-label="Close" onclick="document.getElementById('ch-company-modal').style.display='none'">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <dl>
                    <dt>No:</dt>
                    <dd data-ch="company_number">—</dd>
                    <dt>Status:</dt>
                    <dd data-ch="status">—</dd>
                    <dt>Incorporated:</dt>
                    <dd data-ch="incorporated_on">—</dd>
                    <dt>Registered office:</dt>
                    <dd data-ch="registered_office_address">—</dd>
                    <dt>Accounts due:</dt>
                    <dd data-ch="accounts_due">—</dd>
                    <dt>Confirmation due:</dt>
                    <dd data-ch="confirmation_due">—</dd>
                </dl>
            </div>

            <div class="modal-footer">
                <small class="text-muted">Source: Companies House</small>
            </div>
        </div>
    </div>
</div>

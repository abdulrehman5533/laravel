<div class="modal fade" id="invoiceTypeModal" tabindex="-1" aria-labelledby="invoiceTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="invoiceTypeModalLabel">Select Invoice Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-around">
          <div class="card invoice-type-card" data-type="tax" style="width: 10rem; cursor:pointer;">
            <div class="card-body text-center">
              <i class="bi bi-receipt" style="font-size:2rem;"></i>
              <h6 class="mt-2">Tax Invoice</h6>
              <p class="text-muted small">GST / VAT enabled<br>Legal compliance</p>
            </div>
          </div>
          <div class="card invoice-type-card" data-type="non-tax" style="width: 10rem; cursor:pointer;">
            <div class="card-body text-center">
              <i class="bi bi-file-earmark-text" style="font-size:2rem;"></i>
              <h6 class="mt-2">Non-Tax Invoice</h6>
              <p class="text-muted small">Estimate / Kacha<br>Wholesale friendly</p>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
.invoice-type-card.selected {
  border: 2px solid #0d6efd;
  box-shadow: 0 0 8px #0d6efd33;
}
.invoice-type-card:hover {
  border: 2px solid #0d6efd;
  background: #f8f9fa;
}
</style>
@endpush

@push('scripts')
<script>
let selectedInvoiceType = localStorage.getItem('selectedInvoiceType') || 'tax';

function showInvoiceTypeModal() {
  const modal = new bootstrap.Modal(document.getElementById('invoiceTypeModal'));
  modal.show();
  setTimeout(() => {
    document.querySelectorAll('.invoice-type-card').forEach(card => {
      card.classList.remove('selected');
      if(card.dataset.type === selectedInvoiceType) card.classList.add('selected');
      card.onclick = function() {
        document.querySelectorAll('.invoice-type-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        selectedInvoiceType = card.dataset.type;
        localStorage.setItem('selectedInvoiceType', selectedInvoiceType);
        setTimeout(() => {
          modal.hide();
          document.getElementById('invoice_type').value = selectedInvoiceType;
          document.getElementById('saleForm').submit();
        }, 300);
      };
    });
  }, 200);
}

document.getElementById('saleForm').addEventListener('submit', function(e) {
  if(!document.getElementById('invoice_type').value) {
    e.preventDefault();
    showInvoiceTypeModal();
  }
});
</script>
@endpush

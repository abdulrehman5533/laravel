<!-- Calculator -> POS modal -->
<div class="modal fade" id="calculatorToPosModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Send Calculation to POS</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted">Review calculation details before adding to POS.</p>
        <div id="posCalcSummary"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="posAddBtn">Add to POS</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('calculatorToPosModal');
    const posAddBtn = document.getElementById('posAddBtn');
    let currentCalcId = null;

    window.openCalculatorPosModal = function(calc) {
      // calc can be object or id
      if (typeof calc === 'object' && calc.id) {
        currentCalcId = calc.id;
        document.getElementById('posCalcSummary').innerHTML = `<pre>${JSON.stringify(calc, null, 2)}</pre>`;
      } else {
        currentCalcId = calc;
        document.getElementById('posCalcSummary').innerHTML = `<p class="text-muted">Calculation #${calc}</p>`;
      }
      const bs = new bootstrap.Modal(modalEl); bs.show();
    }

    posAddBtn?.addEventListener('click', async function() {
      if (!currentCalcId) return alert('No calculation selected');
      // Redirect to POS create page with calc param (POS integration varies per installation)
      window.location.href = "{{ route('pos.sales.create') }}?calc=" + encodeURIComponent(currentCalcId);
    });
  });
</script>
@endpush

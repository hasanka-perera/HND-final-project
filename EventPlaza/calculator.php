<?php include 'includes/header.php'; ?>

<section class="hero" style="min-height: 40vh; background: var(--secondary-color);">
    <div class="hero-content">
        <h1>Event Budget Calculator</h1>
        <p>Estimate your event costs effectively.</p>
    </div>
</section>

<div class="container" style="padding: 4rem 0;">
    <div style="background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto;">
        <h2>Calculate Your Expenses</h2>
        <form id="budgetForm">
            <div class="form-group">
                <label>Total Budget ($)</label>
                <input type="number" id="totalBudget" placeholder="e.g. 5000" required>
            </div>
            
            <div id="expenseList">
                <div class="form-group" style="display: flex; gap: 1rem;">
                    <input type="text" placeholder="Item Name (e.g. Catering)" class="item-name" required>
                    <input type="number" placeholder="Cost ($)" class="item-cost" required>
                </div>
            </div>
            
            <button type="button" onclick="addExpenseItem()" style="background: #ccc; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer;">+ Add Item</button>
            
            <div style="margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1rem;">
                <h3>Summary</h3>
                <p>Total Budget: <span id="displayTotal" style="font-weight: bold;">$0</span></p>
                <p>Total Expenses: <span id="displayExpenses" style="font-weight: bold; color: red;">$0</span></p>
                <p>Remaining: <span id="displayRemaining" style="font-weight: bold; color: green;">$0</span></p>
            </div>
        </form>
    </div>
</div>

<script>
function addExpenseItem() {
    const div = document.createElement('div');
    div.className = 'form-group';
    div.style.display = 'flex';
    div.style.gap = '1rem';
    div.innerHTML = `
        <input type="text" placeholder="Item Name" class="item-name" required>
        <input type="number" placeholder="Cost ($)" class="item-cost" required>
        <button type="button" onclick="this.parentElement.remove(); calculateBudget()" style="background: #f8d7da; color: red; border: none; px: 0.5rem; cursor: pointer;">X</button>
    `;
    document.getElementById('expenseList').appendChild(div);
}

function calculateBudget() {
    const total = parseFloat(document.getElementById('totalBudget').value) || 0;
    let expenses = 0;
    document.querySelectorAll('.item-cost').forEach(input => {
        expenses += parseFloat(input.value) || 0;
    });
    
    document.getElementById('displayTotal').innerText = '$' + total.toFixed(2);
    document.getElementById('displayExpenses').innerText = '$' + expenses.toFixed(2);
    
    const remaining = total - expenses;
    const remainingEl = document.getElementById('displayRemaining');
    remainingEl.innerText = '$' + remaining.toFixed(2);
    
    if (remaining < 0) {
        remainingEl.style.color = 'red';
    } else {
        remainingEl.style.color = 'green';
    }
}

document.getElementById('budgetForm').addEventListener('input', calculateBudget);
</script>

<?php include 'includes/footer.php'; ?>

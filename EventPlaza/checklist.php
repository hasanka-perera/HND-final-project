<?php include 'includes/header.php'; ?>

<section class="hero" style="min-height: 40vh; background: var(--secondary-color);">
    <div class="hero-content">
        <h1>Event Checklist</h1>
        <p>Stay organized with your event planning tasks.</p>
    </div>
</section>

<div class="container" style="padding: 4rem 0;">
    <div style="background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto;">
        <h2>My To-Do List</h2>
        <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
            <input type="text" id="taskInput" placeholder="Add a new task..." style="flex: 1; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px;">
            <button onclick="addTask()" class="btn-submit" style="width: auto;">Add</button>
        </div>
        
        <ul id="taskList" style="list-style: none;">
            <!-- Tasks will be added here -->
            <li style="background: #f9f9f9; padding: 1rem; margin-bottom: 0.5rem; border-radius: 5px; display: flex; align-items: center; justify-content: space-between;">
                <span>1. Set a budget</span>
                <button onclick="this.parentElement.remove()" style="color: red; border: none; background: none; cursor: pointer;">Delete</button>
            </li>
             <li style="background: #f9f9f9; padding: 1rem; margin-bottom: 0.5rem; border-radius: 5px; display: flex; align-items: center; justify-content: space-between;">
                <span>2. Create guest list</span>
                <button onclick="this.parentElement.remove()" style="color: red; border: none; background: none; cursor: pointer;">Delete</button>
            </li>
        </ul>
    </div>
</div>

<script>
function addTask() {
    const input = document.getElementById('taskInput');
    const task = input.value.trim();
    if (task) {
        const li = document.createElement('li');
        li.style.cssText = "background: #f9f9f9; padding: 1rem; margin-bottom: 0.5rem; border-radius: 5px; display: flex; align-items: center; justify-content: space-between;";
        li.innerHTML = `
            <span>${task}</span>
            <button onclick="this.parentElement.remove()" style="color: red; border: none; background: none; cursor: pointer;">Delete</button>
        `;
        document.getElementById('taskList').appendChild(li);
        input.value = '';
    }
}
</script>

<?php include 'includes/footer.php'; ?>

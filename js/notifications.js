function fetchPendingTasks() {
    fetch('../php/get-pending-tasks.php')
      .then(response => response.json())
      .then(data => {
        let taskList = document.getElementById('taskList');
        let taskCountBadge = document.getElementById('taskCountBadge');

        if (data.length > 0) {
          taskList.innerHTML = ''; // Clear existing notifications
          data.forEach(task => {
            let li = document.createElement('li');
            li.className = 'dropdown-item';
            li.innerHTML = `<strong>Task ${task.id}:</strong> ${task.request_detail}`;
            taskList.appendChild(li);
          });
          taskCountBadge.textContent = data.length; // Update notification count
          taskCountBadge.style.display = 'inline'; // Show badge
        } else {
          taskList.innerHTML = '<li class="dropdown-item">No pending tasks</li>';
          taskCountBadge.textContent = '0';
          taskCountBadge.style.display = 'none'; // Hide badge when no tasks
        }
      })
      .catch(error => console.error('Error fetching tasks:', error));
  }

  // Refresh notifications every 30 seconds
  setInterval(fetchPendingTasks, 30000);
  fetchPendingTasks(); // Initial call
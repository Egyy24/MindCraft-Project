<?php
?>

<div class="row g-3 my-2">
    <div class="col-md-3 p-1">
        <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
            <div>
                <h3 class="fs-2"><?php echo htmlspecialchars($totalCourses ?? 0); ?></h3>
                <p class="fs-5">Courses</p>
            </div>
            <i class="fas fa-book fs-1 primary-text border rounded-full secondary-bg p-3"></i>
        </div>
    </div>

    <div class="col-md-3 p-1">
        <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
            <div>
                <h3 class="fs-2"><?php echo htmlspecialchars($totalStudents ?? 0); ?></h3>
                <p class="fs-5">Students</p>
            </div>
            <i class="fas fa-users fs-1 primary-text border rounded-full secondary-bg p-3"></i>
        </div>
    </div>

    <div class="col-md-3 p-1">
        <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
            <div>
                <h3 class="fs-2">Rp <?php echo number_format($totalRevenue ?? 0, 0, ',', '.'); ?></h3>
                <p class="fs-5">Revenue</p>
            </div>
            <i class="fas fa-dollar-sign fs-1 primary-text border rounded-full secondary-bg p-3"></i>
        </div>
    </div>

    <div class="col-md-3 p-1">
        <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
            <div>
                <h3 class="fs-2">25%</h3> <p class="fs-5">Growth</p>
            </div>
            <i class="fas fa-chart-line fs-1 primary-text border rounded-full secondary-bg p-3"></i>
        </div>
    </div>
</div>

<div class="row my-5">
    <h3 class="fs-4 mb-3">My Courses</h3>
    <div class="col">
        <div class="table-responsive">
            <table class="table bg-white rounded shadow-sm table-hover">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Thumbnail</th>
                        <th scope="col">Title</th>
                        <th scope="col">Category</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created At</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($courses)): ?>
                        <?php $counter = 1; foreach ($courses as $course): ?>
                            <tr>
                                <th scope="row"><?php echo $counter++; ?></th>
                                <td>
                                    <?php if (!empty($course['thumbnail'])): ?>
                                        <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="Thumbnail" style="width: 80px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/80x50?text=No+Image" alt="No Thumbnail" style="width: 80px; height: 50px; object-fit: cover;">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($course['title']); ?></td>
                                <td><?php echo htmlspecialchars($course['category']); ?></td>
                                <td>
                                    <span class="badge <?php echo ($course['status'] == 'Published') ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                        <?php echo ucfirst(htmlspecialchars($course['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars(date('d M Y', strtotime($course['created_at']))); ?></td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-info text-white me-1" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="#" class="btn btn-sm btn-warning text-white me-1" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="#" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No courses found for this mentor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
?>
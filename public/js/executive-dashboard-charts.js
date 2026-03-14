// Executive Dashboard Charts
window.initializeCharts = function () {
    console.log("Initializing Executive Dashboard Charts...");

    function normalizeSeries(
        labels,
        data,
        fallbackLabel = "No data available",
    ) {
        if (
            !Array.isArray(labels) ||
            !Array.isArray(data) ||
            labels.length === 0 ||
            data.length === 0 ||
            data.every((v) => v === 0)
        ) {
            return {
                labels: [fallbackLabel],
                data: [0],
                isEmpty: true,
            };
        }
        return { labels, data, isEmpty: false };
    }

    // Plugin for "No Data" message overlay
    const noDataPlugin = {
        id: "noDataPlugin",
        afterDraw: (chart) => {
            if (
                chart.data.datasets.length === 0 ||
                (chart.data.datasets[0].data.length <= 1 &&
                    chart.data.datasets[0].data[0] === 0)
            ) {
                const { ctx, width, height } = chart;
                chart.clear();
                ctx.save();
                ctx.textAlign = "center";
                ctx.textBaseline = "middle";
                ctx.font = "16px Inter, system-ui";
                ctx.fillStyle = "#6c757d";
                ctx.fillText(
                    "No data available for this period",
                    width / 2,
                    height / 2,
                );
                ctx.restore();
            }
        },
    };

    // Destroy existing charts to prevent memory leaks and "canvas already in use" errors
    Chart.helpers.each(Chart.instances, function (instance) {
        instance.destroy();
    });

    // Incident Status Chart
    if (
        typeof window.incidentTrackingData !== "undefined" &&
        document.getElementById("incidentStatusChart")
    ) {
        const norm = normalizeSeries(
            window.incidentTrackingData.statusLabels,
            window.incidentTrackingData.statusData,
        );
        const statusCtx = document
            .getElementById("incidentStatusChart")
            .getContext("2d");

        // Explicit color map based on label substring
        const getStatusColor = (label) => {
            const l = label.toLowerCase();
            if (l.includes("resolved")) return "#28a745"; // Green
            if (l.includes("pending")) return "#ff8c8c"; // Red/Pink
            if (l.includes("escalated")) return "#fd7e14"; // Orange
            if (l.includes("ignored")) return "#6c757d"; // Gray
            if (l.includes("reverted")) return "#17a2b8"; // Cyan
            return "#ffc107"; // Yellow default
        };

        new Chart(statusCtx, {
            type: "doughnut",
            plugins: [noDataPlugin],
            data: {
                labels: norm.labels,
                datasets: [
                    {
                        data: norm.data,
                        backgroundColor: norm.isEmpty
                            ? ["#f8f9fa"]
                            : norm.labels.map((label) => getStatusColor(label)),
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "65%",
                onClick: (event, elements) => {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const label = norm.labels[index];
                        // Map labels back to status handles if possible, or just pass labels
                        // The controller handles status searching
                        if (typeof window.showIncidentsByType === "function") {
                            window.showIncidentsByType(
                                label,
                                `Status: ${label}`,
                                { fetchByStatus: "true" },
                            );
                        }
                    }
                },
                onHover: (event, elements) => {
                    event.native.target.style.cursor = elements.length
                        ? "pointer"
                        : "default";
                },
                plugins: {
                    legend: {
                        display: !norm.isEmpty,
                        position: "right",
                        align: "center",
                        labels: {
                            boxWidth: 15,
                            padding: 15,
                            usePointStyle: false,
                            font: { size: 12 },
                        },
                    },
                    tooltip: { enabled: !norm.isEmpty },
                },
            },
        });
    }

    // Incident Priority Chart
    if (
        typeof window.incidentTrackingData !== "undefined" &&
        document.getElementById("incidentPriorityChart")
    ) {
        const norm = normalizeSeries(
            window.incidentTrackingData.priorityLabels,
            window.incidentTrackingData.priorityData,
        );
        const priorityCtx = document
            .getElementById("incidentPriorityChart")
            .getContext("2d");
        new Chart(priorityCtx, {
            type: "doughnut",
            plugins: [noDataPlugin],
            data: {
                labels: norm.labels,
                datasets: [
                    {
                        data: norm.data,
                        backgroundColor: norm.isEmpty
                            ? ["#f8f9fa"]
                            : ["#ff9999", "#ffc107", "#28a745"],
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: !norm.isEmpty },
                    tooltip: { enabled: !norm.isEmpty },
                },
            },
        });
    }

    // Incident Type Chart
    if (
        typeof window.incidentTrackingData !== "undefined" &&
        document.getElementById("incidentTypeChart")
    ) {
        const norm = normalizeSeries(
            window.incidentTrackingData.typeLabels,
            window.incidentTrackingData.typeData,
        );
        const typeCtx = document
            .getElementById("incidentTypeChart")
            .getContext("2d");

        // Color palette for bars (matching image)
        const barColors = [
            "#4A90E2", // Blue
            "#50C878", // Green
            "#FFB347",
            "#9B59B6",
            "#3498DB",
            "#1ABC9C",
            "#F39C12",
            "#E74C3C",
            "#34495E",
            "#16A085",
        ];

        // Generate background colors array - one color per bar
        const backgroundColorArray = norm.isEmpty
            ? ["#f8f9fa"]
            : norm.labels.map(
                  (label, index) => barColors[index % barColors.length],
              );

        // Custom plugin to show values on top of bars
        const chartValueLabels = {
            id: "chartValueLabels",
            afterDatasetsDraw(chart) {
                const { ctx, data } = chart;
                ctx.save();
                ctx.textAlign = "center";
                ctx.textBaseline = "bottom";
                ctx.font = "600 12px Inter, sans-serif";
                ctx.fillStyle = "#2f3542";

                chart.getDatasetMeta(0).data.forEach((bar, index) => {
                    const value = data.datasets[0].data[index];
                    if (value > 0) {
                        ctx.fillText(value, bar.x, bar.y - 5);
                    }
                });
                ctx.restore();
            },
        };

        new Chart(typeCtx, {
            type: "bar",
            plugins: [noDataPlugin, chartValueLabels],
            data: {
                labels: norm.labels,
                datasets: [
                    {
                        label: "Incidents",
                        data: norm.data,
                        backgroundColor: backgroundColorArray,
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onClick: (event, elements) => {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const label = norm.labels[index];
                        const key =
                            window.incidentTrackingData.typeKeys &&
                            window.incidentTrackingData.typeKeys[index]
                                ? window.incidentTrackingData.typeKeys[index]
                                : label;

                        if (typeof window.showIncidentsByType === "function") {
                            window.showIncidentsByType(key, `${label} Details`);
                        }
                    }
                },
                onHover: (event, elements) => {
                    event.native.target.style.cursor = elements.length
                        ? "pointer"
                        : "default";
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: {
                            display: true,
                            color: "rgba(0, 0, 0, 0.05)",
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                    },
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: !norm.isEmpty,
                        backgroundColor: "rgba(0, 0, 0, 0.8)",
                        padding: 10,
                        cornerRadius: 6,
                    },
                },
                layout: {
                    padding: {
                        left: 10,
                        right: 10,
                        top: 10,
                        bottom: 10,
                    },
                },
            },
        });
    }

    // Patrol Type Chart
    if (
        typeof window.patrolAnalyticsData !== "undefined" &&
        document.getElementById("patrolTypeChart")
    ) {
        const norm = normalizeSeries(
            window.patrolAnalyticsData.typeLabels,
            window.patrolAnalyticsData.typeCounts,
        );
        const patrolTypeCtx = document
            .getElementById("patrolTypeChart")
            .getContext("2d");
        new Chart(patrolTypeCtx, {
            type: "bar",
            plugins: [noDataPlugin],
            data: {
                labels: norm.labels,
                datasets: [
                    {
                        label: "Count",
                        data: norm.data,
                        backgroundColor: norm.isEmpty ? "#f8f9fa" : "#28a745",
                    },
                    {
                        label: "Distance (km)",
                        data: norm.isEmpty
                            ? [0]
                            : window.patrolAnalyticsData.typeDistances,
                        backgroundColor: norm.isEmpty ? "#f8f9fa" : "#17a2b8",
                        yAxisID: "y1",
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onClick: (event, elements) => {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const label = norm.labels[index];
                        if (typeof window.showPatrolsByType === "function") {
                            window.showPatrolsByType(
                                label,
                                `Patrols: ${label}`,
                            );
                        }
                    }
                },
                onHover: (event, elements) => {
                    event.native.target.style.cursor = elements.length
                        ? "pointer"
                        : "default";
                },
                scales: {
                    y: { beginAtZero: true },
                    y1: { beginAtZero: true, position: "right" },
                },
            },
        });
    }

    // Daily Patrol Trend Chart
    if (
        typeof window.patrolAnalyticsData !== "undefined" &&
        document.getElementById("dailyPatrolTrendChart")
    ) {
        const norm = normalizeSeries(
            window.patrolAnalyticsData.dailyLabels,
            window.patrolAnalyticsData.dailyCounts,
        );
        const dailyTrendCtx = document
            .getElementById("dailyPatrolTrendChart")
            .getContext("2d");
        new Chart(dailyTrendCtx, {
            type: "line",
            plugins: [noDataPlugin],
            data: {
                labels: norm.labels,
                datasets: [
                    {
                        label: "Patrol Count",
                        data: norm.data,
                        borderColor: norm.isEmpty ? "#f8f9fa" : "#007bff",
                        backgroundColor: norm.isEmpty
                            ? "rgba(248, 249, 250, 0.1)"
                            : "rgba(0, 123, 255, 0.1)",
                        tension: 0.4,
                    },
                    {
                        label: "Distance (km)",
                        data: norm.isEmpty
                            ? [0]
                            : window.patrolAnalyticsData.dailyDistances,
                        borderColor: norm.isEmpty ? "#f8f9fa" : "#28a745",
                        backgroundColor: norm.isEmpty
                            ? "rgba(248, 249, 250, 0.1)"
                            : "rgba(40, 167, 69, 0.1)",
                        yAxisID: "y1",
                        tension: 0.4,
                    },
                ],
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true },
                    y1: { beginAtZero: true, position: "right" },
                },
            },
        });
    }

    // Attendance Trend Chart
    if (
        typeof window.attendanceData !== "undefined" &&
        document.getElementById("attendanceTrendChart")
    ) {
        const norm = normalizeSeries(
            window.attendanceData.dailyLabels,
            window.attendanceData.presentData,
        );
        const attendanceTrendCtx = document
            .getElementById("attendanceTrendChart")
            .getContext("2d");
        new Chart(attendanceTrendCtx, {
            type: "line",
            plugins: [noDataPlugin],
            data: {
                labels: norm.labels,
                datasets: [
                    {
                        label: "Present",
                        data: norm.data,
                        borderColor: norm.isEmpty ? "#f8f9fa" : "#28a745",
                        backgroundColor: norm.isEmpty
                            ? "rgba(248, 249, 250, 0.1)"
                            : "rgba(40, 167, 69, 0.1)",
                        tension: 0.4,
                    },
                    {
                        label: "Absent",
                        data: norm.isEmpty
                            ? [0]
                            : window.attendanceData.absentData,
                        borderColor: norm.isEmpty ? "#f8f9fa" : "#dc3545",
                        backgroundColor: norm.isEmpty
                            ? "rgba(248, 249, 250, 0.1)"
                            : "rgba(220, 53, 69, 0.1)",
                        tension: 0.4,
                    },
                    {
                        label: "Late",
                        data: norm.isEmpty
                            ? [0]
                            : window.attendanceData.lateData,
                        borderColor: norm.isEmpty ? "#f8f9fa" : "#ffc107",
                        backgroundColor: norm.isEmpty
                            ? "rgba(248, 249, 250, 0.1)"
                            : "rgba(255, 193, 7, 0.1)",
                        tension: 0.4,
                    },
                ],
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true },
                },
            },
        });
    }

    // Hourly Distribution Chart
    if (
        typeof window.timePatternsData !== "undefined" &&
        document.getElementById("hourlyDistributionChart")
    ) {
        const norm = normalizeSeries(
            window.timePatternsData.hourlyLabels,
            window.timePatternsData.hourlyData,
        );
        const hourlyCtx = document
            .getElementById("hourlyDistributionChart")
            .getContext("2d");
        new Chart(hourlyCtx, {
            type: "bar",
            plugins: [noDataPlugin],
            data: {
                labels: norm.labels,
                datasets: [
                    {
                        label: "Patrol Count",
                        data: norm.data,
                        backgroundColor: norm.isEmpty ? "#f8f9fa" : "#17a2b8",
                    },
                ],
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true },
                },
            },
        });
    }
};

// Initial run
document.addEventListener("DOMContentLoaded", function () {
    window.initializeCharts();
});

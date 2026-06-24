import ApexCharts from "apexcharts";
window.ApexCharts = ApexCharts;

document.addEventListener("alpine:init", () => {
    Alpine.data("weatherChart", (initial) => ({
        chart: null,

        isDark() {
            return document.documentElement.classList.contains("dark");
        },

        themeColors() {
            const dark = this.isDark();
            return {
                label:  dark ? "#a3a3a3" : "#64748b",
                grid:   dark ? "#404040" : "#f1f5f9",
                tooltip: dark ? "dark" : "light",
                legend: dark ? "#c2c2c2" : "#334155",
            };
        },

        buildOptions(data) {
            const c = this.themeColors();
            return {
                chart: {
                    type: "area",
                    height: 300,
                    fontFamily: "inherit",
                    toolbar: { show: false },
                    animations: { enabled: true, easing: "easeinout", speed: 400 },
                    background: "transparent",
                },
                theme: { mode: this.isDark() ? "dark" : "light" },
                stroke: { curve: "smooth", width: 2.5 },
                fill: {
                    type: "gradient",
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.25,
                        opacityTo: 0.02,
                        stops: [0, 100],
                    },
                },
                colors: [data.color],
                series: [{ name: `${data.label} (${data.unit})`, data: data.data }],
                xaxis: {
                    categories: data.categories,
                    labels: { style: { colors: c.label, fontSize: "11px" } },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: {
                    labels: {
                        style: { colors: c.label, fontSize: "11px" },
                        formatter: (val) => `${val} ${data.unit}`,
                    },
                },
                legend: { show: false },
                grid: { borderColor: c.grid, strokeDashArray: 4 },
                tooltip: {
                    theme: c.tooltip,
                    y: { formatter: (val) => `${val} ${data.unit}` },
                },
                dataLabels: { enabled: false },
                markers: { size: 0, hover: { size: 4 } },
            };
        },

        init() {
            this.chart = new ApexCharts(this.$refs.chart, this.buildOptions(initial));
            this.chart.render();

            // Re-theme saat toggle dark/light
            this.observer = new MutationObserver(() => {
                const c = this.themeColors();
                this.chart.updateOptions({
                    theme: { mode: this.isDark() ? "dark" : "light" },
                    xaxis: { labels: { style: { colors: c.label } } },
                    yaxis: { labels: { style: { colors: c.label } } },
                    grid: { borderColor: c.grid },
                    tooltip: { theme: c.tooltip },
                }, false, false);
            });
            this.observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ["class"],
            });

            // Update chart saat Livewire push data baru
            Livewire.on("chart-updated", ({ chart }) => {
                this.chart.updateOptions({
                    colors: [chart.color],
                    xaxis: { categories: chart.categories },
                    yaxis: {
                        labels: {
                            formatter: (val) => `${val} ${chart.unit}`,
                        },
                    },
                    tooltip: {
                        y: { formatter: (val) => `${val} ${chart.unit}` },
                    },
                }, false, true);
                this.chart.updateSeries([
                    { name: `${chart.label} (${chart.unit})`, data: chart.data },
                ]);
            });
        },
    }));
});

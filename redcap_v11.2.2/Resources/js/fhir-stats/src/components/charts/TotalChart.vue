<template>
  <div>
  <canvas ref="chart" />

  </div>
</template>

<script>
import {Chart, registerables} from 'chart.js'
Chart.register(...registerables)



export default {
    data: () => ({
        chart: null, // register the canvas context. needed to destroy before update
    }),
    computed: {},
    methods: {
        makeChart() {
            const ctx = this.$refs['chart']
            if(!ctx) return
            if(this.chart) this.chart.destroy()
            this.chart = new Chart(ctx, {
                type: 'bar',
                data: this.chartdata,
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Total entries',// Entries per day
                        },
                    },
                    interaction: {
                        intersect: false,
                        mode: 'dataset'
                    },
                    x: {
                        title: {
                        display: true,
                        text: 'Day'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Entries'
                        },
                        /* min: 0,
                        max: 100,
                        ticks: {
                            // forces step size to be 50 units
                            stepSize: 50
                        } */
                    }
                }
            })
            return this.chart
        }
    },
    props: {
        chartdata: {
            type: Object,
            default: null
        },
        options: {
            type: Object,
            default: () => ({
                responsive: true,
                maintainAspectRatio: false
            })
        }
    },
    watch: {
        chartdata: {
            immediate: true,
            deep: true,
            handler() {
                this.makeChart()
            }
        }
    }
}
</script>

<style>

</style>
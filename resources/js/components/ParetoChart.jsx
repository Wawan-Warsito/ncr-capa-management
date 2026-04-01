import React from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, ComposedChart, Line } from 'recharts';

const ParetoChart = ({ data }) => {
    if (!data || data.length === 0) {
        return (
            <div className="flex items-center justify-center h-full">
                <p className="text-gray-500">No defect data available</p>
            </div>
        );
    }

    // Process data for Pareto: Sort by count desc, calculate cumulative percentage
    const processedData = [...data].sort((a, b) => b.total - a.total);
    const totalCount = processedData.reduce((sum, item) => sum + item.total, 0);
    
    let cumulativeCount = 0;
    const chartData = processedData.map(item => {
        cumulativeCount += item.total;
        return {
            name: item.defect_category?.category_name || 'Unknown',
            count: item.total,
            cumulativePercentage: Math.round((cumulativeCount / totalCount) * 100)
        };
    });

    return (
        <div className="h-full w-full">
            <h3 className="text-lg font-medium text-gray-900 mb-4">Pareto Chart (Defect Categories)</h3>
            <ResponsiveContainer width="100%" height="85%">
                <ComposedChart
                    data={chartData}
                    margin={{ top: 20, right: 20, bottom: 20, left: 20 }}
                >
                    <CartesianGrid stroke="#f5f5f5" />
                    <XAxis dataKey="name" scale="band" angle={-45} textAnchor="end" height={60} interval={0} fontSize={10} />
                    <YAxis yAxisId="left" orientation="left" stroke="#8884d8" />
                    <YAxis yAxisId="right" orientation="right" stroke="#82ca9d" unit="%" />
                    <Tooltip />
                    <Legend />
                    <Bar yAxisId="left" dataKey="count" barSize={20} fill="#413ea0" name="Frequency" />
                    <Line yAxisId="right" type="monotone" dataKey="cumulativePercentage" stroke="#ff7300" name="Cumulative %" />
                </ComposedChart>
            </ResponsiveContainer>
        </div>
    );
};

export default ParetoChart;

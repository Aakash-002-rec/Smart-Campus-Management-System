import React, { useState, useEffect } from 'react';

export default function App() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('../backend/student/api_student_data.php')
      .then(res => res.json())
      .then(json => {
        setData(json.data);
        setLoading(false);
      })
      .catch(err => {
        console.error('Error fetching analytics API:', err);
        setLoading(false);
      });
  }, []);

  if (loading) return <div className="p-5 text-center">Loading Analytics Engine...</div>;
  if (!data) return <div className="p-5 text-center text-danger">Failed to load data.</div>;

  return (
    <div className="container py-4">
      <h2 className="fw-bold mb-3">React Analytics & Early Warning Dashboard</h2>
      <div className="alert alert-warning">
        <strong>Status:</strong> {data.stats.risk_level}
      </div>
    </div>
  );
}

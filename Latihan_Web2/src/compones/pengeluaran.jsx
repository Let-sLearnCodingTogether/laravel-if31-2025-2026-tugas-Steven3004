import React, { useCallback } from 'react'
import { useEffect } from 'react';
import api from '../api/api.js';

export default function pengeluaran() {

  const [loading, setLoading] = React.useState(false);
  const [pengeluaran, setPengeluaran] = React.useState([]);

  const fetchPengeluaran = useCallback(async () => {
    try {
      setLoading(true)
      const response = await api.get('/expenses');
      console.log(response.data);
      setPengeluaran(response.data);

    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false)
    }
  }, []);

  const handleDelete = async (id) => {
    try {
      const response = await api.delete(`/expenses/${id}`);
      if (response.status === 200) {
        fetchPengeluaran();
      }
    } catch (error) {
      console.error(error);
    }
  }

  useEffect(() => {
    fetchPengeluaran();
  }, [fetchPengeluaran]);

  return (
    <div>
      <h1>Liat Pengeluaran</h1>

      <table>
        <thead>
          <tr>
            <th>Description</th>
            <th>amount</th>
            <th>date</th>
            <th>category</th>
          </tr>
        </thead>
        <tbody>
          {
          pengeluaran.map((item, index) => {
            return (
              <tr key={item.id}>
                <td>{index +1}</td>
                <td>{item.description}</td>
                <td>{item.amount}</td>
                <td>{item.date}</td>
                <td>{item.category}</td>
                <td>
                  <button onClick={() => handleDelete(item.id)}>Delete</button>
                </td>
              </tr>
            )
          })
        }
        </tbody>
      </table>
    </div>
  )
}


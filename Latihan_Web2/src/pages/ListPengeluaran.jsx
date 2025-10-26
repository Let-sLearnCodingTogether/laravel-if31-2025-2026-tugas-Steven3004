import React from 'react'
import { useState, useEffect, useCallback } from 'react';
import api from '../api/api.js';

export default function ListPengeluaran() {
    const [loading, setLoading] = useState(false);

    const [formData, setFormData] = useState({
        description: '',
        amount: '',
        date: '',
        category: ''
    });

    const handleInputChange = async (event) => {
        const {value, name} = event.target;

        setFormData({
            ...formData,
            [name]: value
        })
    }
    const handleSubmit = async (event) => {
        event.preventDefault()
        try {
            setLoading(true)

            const response = await api.post('/expenses', formData)

            console.log(response)

            if (response.status === 201) {
                setFormData({
                    description: '',
                    amount: '',
                    date: '',
                    category: ''
                });
                
            }

        } catch (error) {
            console.error(error);
        }finally {
            setLoading(false)
        }
    };

  return (
    <div>
        <h1>Pengeluaran Harian</h1>
        <form onSubmit={handleSubmit}>
            <div>
                <label>Description:</label>
                <input name='description' type="text" placeholder="Description" value={formData.description} onChange={handleInputChange} />
            </div>
            <div>
                <label>Amount:</label>
                <input name='amount' type="number" placeholder="Amount" value={formData.amount} onChange={handleInputChange} />
            </div>
            <div>
                <label>Date:</label>
                <input name='date' type="date" placeholder="Date" value={formData.date} onChange={handleInputChange} />
            </div>
            <div>
                <label>Category:</label>
                <input name='category' type="text" placeholder="Category" value={formData.category} onChange={handleInputChange} />
            </div>
            <button type="submit">Simpan</button>
        </form>
        
    </div>
  )
}

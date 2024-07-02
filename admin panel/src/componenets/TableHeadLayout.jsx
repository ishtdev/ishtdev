import React from 'react'

export default function TableHeadLayout(props) {
  return (
          <thead>
            <tr>
              <th>Sr No.</th>
              <th>Profile ID</th>
              <th>{props.name}</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
         
  )
}
